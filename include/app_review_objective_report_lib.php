<?php
require_once __DIR__ . '/invoice_report_lib.php';

function appReviewObjectiveSqlDate($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    foreach (array('d.m.Y', 'Y-m-d') as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value);
        $errors = DateTime::getLastErrors();
        if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d');
        }
    }

    throw new InvalidArgumentException('Please select a valid date.');
}

function appReviewObjectiveDisplayDate($value) {
    if (empty($value) || $value === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d.m.Y', $timestamp) : (string) $value;
}

function appReviewObjectiveDays($receivedDate, $reviewDate) {
    if (empty($receivedDate) || empty($reviewDate)
        || $receivedDate === '0000-00-00' || $reviewDate === '0000-00-00') {
        return null;
    }

    try {
        $received = new DateTime($receivedDate);
        $review = new DateTime($reviewDate);
        return (int) $received->diff($review)->format('%a');
    } catch (Exception $e) {
        return null;
    }
}

function appReviewObjectiveFilterText($fromDate, $toDate, $clientName) {
    $filters = array();
    if ($fromDate !== '' || $toDate !== '') {
        $filters[] = 'For Period: ' . ($fromDate !== '' ? $fromDate : 'Start')
            . ' to ' . ($toDate !== '' ? $toDate : 'End');
    }
    if ($clientName !== '') {
        $filters[] = 'Client: ' . strtoupper($clientName);
    }

    return empty($filters) ? 'All Records' : implode(', ', $filters);
}

function fetchAppReviewObjectiveReportData($db, $fromDate, $toDate, $clientName = '') {
    $sql = "SELECT ci.id AS customer_id, ci.customer_name, svc.service_id,
                   aa.audit_id, aa.application_rcv_date, aa.review_date, aa.app_obj_comment
            FROM customer_info ci
            INNER JOIN service svc ON ci.id = svc.customer_id
            INNER JOIN audit_application aa ON aa.audit_id = (
                SELECT MAX(aa_latest.audit_id)
                FROM audit_application aa_latest
                WHERE aa_latest.uni_svc_id = svc.service_id
            )
            WHERE ci.is_deleted = 'NO'
              AND svc.service_type = 'CR'";
    $params = array();

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaffA = in_array($invRole, array('Office Staff-A'), true)
        || strtolower(str_replace(array(' ', '-'), '', $invRole)) === 'officestaffa';
    $isRestrictedRole = $isMarketingHead || $isOfficeStaffA;

    $fromDateSql = appReviewObjectiveSqlDate($fromDate);
    $toDateSql = appReviewObjectiveSqlDate($toDate);
    if ($fromDateSql !== '' && $toDateSql !== '' && $fromDateSql > $toDateSql) {
        throw new InvalidArgumentException('From Date cannot be after To Date.');
    }

    if ($fromDateSql !== '') {
        $sql .= " AND aa.application_rcv_date >= :fromDate";
        $params[':fromDate'] = $fromDateSql;
    }
    if ($toDateSql !== '') {
        $sql .= " AND aa.application_rcv_date <= :toDate";
        $params[':toDate'] = $toDateSql;
    }
    if (trim((string) $clientName) !== '') {
        $params[':clientName'] = '%' . trim($clientName) . '%';
        if ($isRestrictedRole && $fromDateSql === '' && $toDateSql === '') {
            $params[':exactClient'] = trim($clientName);
            $sql .= " AND (ci.customer_name = :exactClient OR aa.application_rcv_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')) AND ci.customer_name LIKE :clientName";
        } else {
            $sql .= " AND ci.customer_name LIKE :clientName";
        }
    } else {
        if ($isRestrictedRole && $fromDateSql === '' && $toDateSql === '') {
            $sql .= " AND aa.application_rcv_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')";
        }
    }

    $sql .= " ORDER BY aa.application_rcv_date DESC, ci.customer_name ASC";

    $statement = $db->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function buildAppReviewObjectiveReportXlsxBytes($records, $fromDate, $toDate, $clientName = '') {
    $xlsx = new SimpleXlsx();
    $xlsx->freezePanesAbove(5);
    $xlsx->setPageLayout('landscape', 1, 0);

    $xlsx->setColWidth(1, 7);
    $xlsx->setColWidth(2, 38);
    $xlsx->setColWidth(3, 22);
    $xlsx->setColWidth(4, 22);
    $xlsx->setColWidth(5, 12);
    $xlsx->setColWidth(6, 52);

    $xlsx->addRow(array(array(
        'v' => 'AITF MANAGEMENT CERTIFICATION LIMITED',
        's' => 1,
        't' => 's'
    )));
    $xlsx->merge('A1:F1');
    $xlsx->setRowHeight(1, 28);

    $xlsx->addRow(array(array(
        'v' => 'APPLICATION REVIEW OBJECTIVE REPORT ('
            . appReviewObjectiveFilterText($fromDate, $toDate, $clientName) . ')',
        's' => 2,
        't' => 's'
    )));
    $xlsx->merge('A2:F2');
    $xlsx->setRowHeight(2, 22);

    $accentRow = array();
    for ($column = 0; $column < 6; $column++) {
        $accentRow[] = array('v' => '', 's' => 38, 't' => 's');
    }
    $xlsx->addRow($accentRow);
    $xlsx->setRowHeight(3, 8);

    $headers = array(
        'Sr.',
        'Client Name',
        'Application Received Date',
        'Review of Application Date',
        'Days',
        'Remark(s), if any'
    );
    $headerRow = array();
    foreach ($headers as $header) {
        $headerRow[] = array('v' => $header, 's' => 4, 't' => 's');
    }
    $xlsx->addRow($headerRow);
    $xlsx->setRowHeight(4, 30);

    $withinTarget = 0;
    $delayed = 0;
    $pendingReview = 0;
    $rowNumber = 5;

    if (empty($records)) {
        $xlsx->addRow(array(array('v' => 'No records found', 's' => 7, 't' => 's')));
        $xlsx->merge('A5:F5');
    } else {
        $serial = 1;
        foreach ($records as $record) {
            $days = appReviewObjectiveDays($record['application_rcv_date'], $record['review_date']);
            if ($days === null) {
                $pendingReview++;
                $centerStyle = 7;
                $clientStyle = 6;
                $textStyle = 5;
                $daysValue = '-';
                $daysType = 's';
            } elseif ($days > 2) {
                $delayed++;
                $centerStyle = 32;
                $clientStyle = 33;
                $textStyle = 34;
                $daysValue = $days;
                $daysType = 'n';
            } else {
                $withinTarget++;
                $centerStyle = 26;
                $clientStyle = 27;
                $textStyle = 28;
                $daysValue = $days;
                $daysType = 'n';
            }

            $xlsx->addRow(array(
                array('v' => $serial++, 's' => $centerStyle, 't' => 'n'),
                array('v' => $record['customer_name'], 's' => $clientStyle, 't' => 's'),
                array('v' => appReviewObjectiveDisplayDate($record['application_rcv_date']), 's' => $centerStyle, 't' => 's'),
                array('v' => appReviewObjectiveDisplayDate($record['review_date']), 's' => $centerStyle, 't' => 's'),
                array('v' => $daysValue, 's' => $centerStyle, 't' => $daysType),
                array('v' => (string) $record['app_obj_comment'], 's' => $textStyle, 't' => 's')
            ));
            $xlsx->setRowHeight($rowNumber++, 34);
        }
    }

    $lastDataRow = empty($records) ? 5 : 4 + count($records);
    $xlsx->setAutoFilter('A4:F' . $lastDataRow);
    $xlsx->addRow(array(array('v' => '', 's' => 0, 't' => 's')));

    $summaryRow = $lastDataRow + 2;
    $xlsx->addRow(array(array('v' => 'REPORT SUMMARY', 's' => 16, 't' => 's')));
    $xlsx->merge('A' . $summaryRow . ':D' . $summaryRow);
    $xlsx->setRowHeight($summaryRow, 24);

    $summary = array(
        array('TOTAL RECORDS', count($records)),
        array('REVIEWED WITHIN 2 DAYS', $withinTarget),
        array('REVIEWED AFTER 2 DAYS', $delayed),
        array('REVIEW DATE PENDING', $pendingReview)
    );
    foreach ($summary as $line) {
        $summaryRow++;
        $xlsx->addRow(array(
            array('v' => $line[0], 's' => 12, 't' => 's'),
            null,
            null,
            array('v' => $line[1], 's' => 20, 't' => 'n')
        ));
        $xlsx->merge('A' . $summaryRow . ':C' . $summaryRow);
        $xlsx->setRowHeight($summaryRow, 22);
    }

    return $xlsx->toString('Application Review');
}

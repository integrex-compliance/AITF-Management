<?php

require_once __DIR__ . '/invoice_report_lib.php';

function auditorChecklistExcelText($value, $maxLength = 6000)
{
    $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    $value = preg_replace('/[ \t]+/', ' ', $value);
    $value = preg_replace('/\s*\r?\n\s*/', "\n", $value);
    $value = trim($value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function auditorChecklistExcelDate($value)
{
    $value = auditorChecklistExcelText($value, 20);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('d.m.Y', $timestamp) : $value;
}

function auditorChecklistExcelStatusKey($value)
{
    return strtolower(preg_replace('/[^a-z0-9]+/i', '', auditorChecklistExcelText($value, 100)));
}

function auditorChecklistExcelFilterText(array $filters)
{
    $parts = array();
    $labels = array(
        'clientName' => 'Client',
        'certificateNo' => 'Certificate',
        'iafNace' => 'IAF/NACE',
        'auditor' => 'Auditor',
        'accreditation' => 'Accreditation',
        'status' => 'Status',
    );

    foreach ($labels as $key => $label) {
        $value = auditorChecklistExcelText($filters[$key] ?? '', 120);
        if ($value !== '') {
            $parts[] = $label . ': ' . $value;
        }
    }

    $dateFrom = auditorChecklistExcelDate($filters['dateFrom'] ?? '');
    $dateTo = auditorChecklistExcelDate($filters['dateTo'] ?? '');
    if ($dateFrom !== '' || $dateTo !== '') {
        $parts[] = 'Audit Period: ' . ($dateFrom !== '' ? $dateFrom : 'Start') . ' to ' . ($dateTo !== '' ? $dateTo : 'End');
    }

    $text = empty($parts) ? 'All Records' : implode(', ', $parts);
    return auditorChecklistExcelText($text, 500);
}

function auditorChecklistExcelRowHeight(array $row)
{
    $longest = 0;
    foreach (array('client', 'certificate_no', 'contact_person', 'scope', 'iaf_nace', 'auditor') as $key) {
        $length = function_exists('mb_strlen')
            ? mb_strlen((string)($row[$key] ?? ''), 'UTF-8')
            : strlen((string)($row[$key] ?? ''));
        $longest = max($longest, $length);
    }

    if ($longest > 240) {
        return 60;
    }
    if ($longest > 140) {
        return 46;
    }
    if ($longest > 70) {
        return 34;
    }

    return 24;
}

function buildAuditorChecklistXlsxBytes(array $rows, array $filters = array())
{
    $xlsx = new SimpleXlsx();
    $columnCount = 11;

    $xlsx->freezePanesAbove(5);
    $xlsx->setPageLayout('landscape', 1, 0);

    $widths = array(6, 32, 23, 24, 16, 48, 18, 30, 13, 14, 17);
    foreach ($widths as $index => $width) {
        $xlsx->setColWidth($index + 1, $width);
    }

    $xlsx->addRow(array(array('v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's')));
    $xlsx->merge('A1:K1');
    $xlsx->setRowHeight(1, 28);

    $xlsx->addRow(array(array(
        'v' => 'AUDITOR CHECKLIST REPORT (' . auditorChecklistExcelFilterText($filters) . ')',
        's' => 2,
        't' => 's',
    )));
    $xlsx->merge('A2:K2');
    $xlsx->setRowHeight(2, 24);

    $accentRow = array();
    for ($column = 0; $column < $columnCount; $column++) {
        $accentRow[] = array('v' => '', 's' => 38, 't' => 's');
    }
    $xlsx->addRow($accentRow);
    $xlsx->setRowHeight(3, 8);

    $headers = array(
        'Sr.',
        'Client Name',
        'Certificate No.',
        'Contact Person',
        'Accreditation',
        'Scope',
        'IAF/NACE',
        'Auditor Name',
        'Audit Type',
        'Audit Date',
        'Status',
    );
    $headerRow = array();
    foreach ($headers as $header) {
        $headerRow[] = array('v' => $header, 's' => 4, 't' => 's');
    }
    $xlsx->addRow($headerRow);
    $xlsx->setRowHeight(4, 30);

    $auditTypeCounts = array();
    $statusCounts = array();
    $currentRow = 4;

    if (empty($rows)) {
        $currentRow++;
        $xlsx->addRow(array(array('v' => 'No records found', 's' => 7, 't' => 's')));
        $xlsx->merge('A' . $currentRow . ':K' . $currentRow);
        $xlsx->setRowHeight($currentRow, 26);
    } else {
        $serial = 1;
        foreach ($rows as $sourceRow) {
            if (!is_array($sourceRow)) {
                continue;
            }

            $row = array(
                'client' => auditorChecklistExcelText($sourceRow['client'] ?? '', 1000),
                'certificate_no' => auditorChecklistExcelText($sourceRow['certificate_no'] ?? '', 1000),
                'contact_person' => auditorChecklistExcelText($sourceRow['contact_person'] ?? '', 500),
                'accreditation' => auditorChecklistExcelText($sourceRow['accreditation'] ?? '', 100),
                'scope' => auditorChecklistExcelText($sourceRow['scope'] ?? '', 6000),
                'iaf_nace' => auditorChecklistExcelText($sourceRow['iaf_nace'] ?? '', 1000),
                'auditor' => auditorChecklistExcelText($sourceRow['auditor'] ?? '', 1500),
                'audit_type' => auditorChecklistExcelText($sourceRow['audit_type'] ?? '', 100),
                'audit_date' => auditorChecklistExcelDate($sourceRow['audit_date'] ?? ''),
                'status' => auditorChecklistExcelText($sourceRow['status'] ?? '', 100),
            );

            $auditTypeLabel = $row['audit_type'] !== '' ? strtoupper($row['audit_type']) : 'NOT SPECIFIED';
            $statusLabel = $row['status'] !== '' ? strtoupper($row['status']) : 'NOT SPECIFIED';
            $auditTypeCounts[$auditTypeLabel] = ($auditTypeCounts[$auditTypeLabel] ?? 0) + 1;
            $statusCounts[$statusLabel] = ($statusCounts[$statusLabel] ?? 0) + 1;

            $statusKey = auditorChecklistExcelStatusKey($row['status']);
            if ($statusKey === 'active') {
                $centerStyle = 26;
                $clientStyle = 27;
                $textStyle = 28;
                $statusStyle = 31;
            } elseif (
                strpos($statusKey, 'withdraw') !== false
                || strpos($statusKey, 'expire') !== false
                || strpos($statusKey, 'suspend') !== false
                || strpos($statusKey, 'cancel') !== false
            ) {
                $centerStyle = 32;
                $clientStyle = 33;
                $textStyle = 34;
                $statusStyle = 37;
            } else {
                $centerStyle = 7;
                $clientStyle = 6;
                $textStyle = 5;
                $statusStyle = 10;
            }

            $xlsx->addRow(array(
                array('v' => $serial++, 's' => $centerStyle, 't' => 'n'),
                array('v' => $row['client'] !== '' ? $row['client'] : '-', 's' => $clientStyle, 't' => 's'),
                array('v' => $row['certificate_no'] !== '' ? $row['certificate_no'] : '-', 's' => $textStyle, 't' => 's'),
                array('v' => $row['contact_person'] !== '' ? $row['contact_person'] : '-', 's' => $textStyle, 't' => 's'),
                array('v' => $row['accreditation'] !== '' ? $row['accreditation'] : '-', 's' => $centerStyle, 't' => 's'),
                array('v' => $row['scope'] !== '' ? $row['scope'] : '-', 's' => $textStyle, 't' => 's'),
                array('v' => $row['iaf_nace'] !== '' ? $row['iaf_nace'] : '-', 's' => $textStyle, 't' => 's'),
                array('v' => $row['auditor'] !== '' ? $row['auditor'] : '-', 's' => $textStyle, 't' => 's'),
                array('v' => $row['audit_type'] !== '' ? $row['audit_type'] : '-', 's' => $centerStyle, 't' => 's'),
                array('v' => $row['audit_date'] !== '' ? $row['audit_date'] : '-', 's' => $centerStyle, 't' => 's'),
                array('v' => $row['status'] !== '' ? $row['status'] : '-', 's' => $statusStyle, 't' => 's'),
            ));

            $currentRow++;
            $xlsx->setRowHeight($currentRow, auditorChecklistExcelRowHeight($row));
        }
    }

    $lastDataRow = max(4, $currentRow);
    $xlsx->setAutoFilter('A4:K' . $lastDataRow);

    $currentRow++;
    $xlsx->addRow(array(array('v' => '', 's' => 0, 't' => 's')));

    $currentRow++;
    $xlsx->addRow(array(array('v' => 'AUDITOR CHECKLIST SUMMARY', 's' => 16, 't' => 's')));
    $xlsx->merge('A' . $currentRow . ':K' . $currentRow);
    $xlsx->setRowHeight($currentRow, 24);

    $summaryLines = array(array('TOTAL RECORDS', count($rows)));
    ksort($auditTypeCounts);
    foreach ($auditTypeCounts as $auditType => $count) {
        $summaryLines[] = array('AUDIT TYPE - ' . $auditType, $count);
    }
    ksort($statusCounts);
    foreach ($statusCounts as $status => $count) {
        $summaryLines[] = array('STATUS - ' . $status, $count);
    }

    foreach ($summaryLines as $line) {
        $currentRow++;
        $xlsx->addRow(array(
            array('v' => $line[0], 's' => 12, 't' => 's'),
            null,
            null,
            array('v' => $line[1], 's' => 20, 't' => 'n'),
        ));
        $xlsx->merge('A' . $currentRow . ':C' . $currentRow);
        $xlsx->setRowHeight($currentRow, 22);
    }

    return $xlsx->toString('Auditor Checklist');
}


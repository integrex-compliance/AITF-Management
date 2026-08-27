<?php
header('Content-Type: application/json');
include_once './dbConnect.php';
include_once './commonutil.php';
require_once('tcpdf_include.php');
include_once __DIR__ . '/include/invoice_mail_helper.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hp_role = trim($_SESSION['role'] ?? '');
$isMarketingHead = in_array($hp_role, array('Marketing Head', 'Marketing Executive'), true)
    || strtolower(str_replace(' ', '', $hp_role)) === 'marketinghead'
    || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
$isOfficeStaff = in_array($hp_role, array('Office Staff-A', 'Office Staff-B'), true)
    || in_array(strtolower(str_replace(array(' ', '-'), '', $hp_role)), array('officestaffa', 'officestaffb'), true);

$fromDate = $_POST['fromDate'] ?? '';
$toDate = $_POST['toDate'] ?? '';
$serviceType = $_POST['serviceType'] ?? '';
$clientName = $_POST['clientName'] ?? '';
$statusFilter = $_POST['statusFilter'] ?? '';
$format = strtolower($_POST['format'] ?? 'excel');
$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.');
}
if ($format !== 'pdf' && $format !== 'excel') {
    $format = 'excel';
}

if (!function_exists('hpNormalizeStatus')) {
    function hpNormalizeStatus($value) {
        $status = trim((string) $value);
        if ($status === '') return 'No Status';
        $key = strtolower(str_replace(array('-', '_', ' '), '', $status));
        if ($key === 'active' || $key === 'valid' || $key === 'granted') return 'Active';
        if ($key === 'suspended' || $key === 'suspend') return 'Suspended';
        if ($key === 'inprocess') return 'In-process';
        if ($key === 'contractinprocess') return 'Contract In-process';
        if ($key === 'withdrawn' || $key === 'withdraw') return 'Withdrawn';
        if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled') return 'Application canceled';
        if ($key === 'expired' || $key === 'expire') return 'Expired';
        return $status;
    }
}
if (!function_exists('hpDerivedStatus')) {
    function hpDerivedStatus($customer, $serviceMap) {
        $norm = hpNormalizeStatus(isset($customer['audit_status']) ? $customer['audit_status'] : '');
        if ($norm === 'Application canceled') return 'Application canceled';
        $cid = isset($customer['id']) ? $customer['id'] : null;
        $svc = ($cid !== null && isset($serviceMap[$cid])) ? $serviceMap[$cid] : null;
        if (empty($svc)) return 'New Enquiry';
        if (isset($svc['service_type']) && strtoupper(trim($svc['service_type'])) === 'CR') return 'Contract-Review';
        return $norm;
    }
}

try {
    $customerList = "SELECT ci.* , ac.certification_date FROM customer_info ci left join allotment_count ac on ci.id = ac.client_id where is_deleted <> 'YES' ORDER BY ac.certification_date DESC";
    $statement = $db->prepare($customerList);
    $statement->execute();
    $listOfCustomer = $statement->fetchAll();

    $users = "SELECT * FROM `handler`";
    $statement = $db->prepare($users);
    $statement->execute();
    $allUsers = $statement->fetchAll();

    $handlerMap = array();
    foreach ($allUsers as $handlerRow) {
        $handlerMap[$handlerRow['id']] = $handlerRow['handler_name'];
    }

    $customerIds = array_column($listOfCustomer, 'id');
    $serviceMap = array();
    $serviceNonSpMap = array();
    $dueMap = array();

    if (!empty($customerIds)) {
        $inPlaceholders = implode(',', array_fill(0, count($customerIds), '?'));
        $sqlLatestService = "SELECT s.* FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlLatestService);
        $stmt->execute($customerIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceMap[$row['customer_id']] = $row;
        }

        $sqlNonSpService = "SELECT s.customer_id, s.service_id FROM service s INNER JOIN (SELECT customer_id, MAX(service_id) AS max_sid FROM service WHERE service_type <> 'SP' AND customer_id IN ($inPlaceholders) GROUP BY customer_id) t ON s.customer_id = t.customer_id AND s.service_id = t.max_sid";
        $stmt = $db->prepare($sqlNonSpService);
        $stmt->execute($customerIds);
        $serviceIdsForDates = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $serviceNonSpMap[$row['customer_id']] = $row['service_id'];
            $serviceIdsForDates[] = $row['service_id'];
        }

        if (!empty($serviceIdsForDates)) {
            $inServiceIds = implode(',', array_fill(0, count($serviceIdsForDates), '?'));
            $sqlDates = "SELECT service_id, action, due_date FROM ca_service_dates WHERE service_id IN ($inServiceIds) AND TRIM(action) IN ('Suspension Letter','Suspend Letter','Expire Letter','Withdrawal Letter','Withdraw Letter')";
            $stmt = $db->prepare($sqlDates);
            $stmt->execute($serviceIdsForDates);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $dueMap[$row['service_id']][trim($row['action'])] = $row['due_date'];
            }
        }
    }

    $filteredCustomers = [];
    foreach ($listOfCustomer as $customer) {
        $derivedStatus = hpDerivedStatus($customer, $serviceMap);

        if ($isMarketingHead && in_array($derivedStatus, array('Active', 'Withdrawn', 'Withdrawal', 'Withdraw'), true)) {
            continue;
        }

        $isCurrentOrRecent = empty($customer['certification_date']) || ($customer['certification_date'] >= date('Y-m-01', strtotime('-1 month')));
        if ($clientName != '') {
            $searchVal = strtolower(trim($clientName));
            $custName = strtolower(trim($customer['customer_name'] ?? ''));
            $uniqId = strtolower(trim($customer['unique_id'] ?? ''));
            $isMatch = (stripos($custName, $searchVal) !== false || stripos($uniqId, $searchVal) !== false);
            if (!$isMatch) {
                continue;
            }
            if ($isOfficeStaff && !$isCurrentOrRecent) {
                if ($custName !== $searchVal && $uniqId !== $searchVal) {
                    continue;
                }
            }
        } else {
            if ($isOfficeStaff && empty($fromDate) && empty($toDate)) {
                if (!$isCurrentOrRecent) {
                    continue;
                }
            }
        }
        if ($serviceType != '' && strcasecmp($customer['next_service'], $serviceType) !== 0) continue;
        if ($statusFilter != '') {
            if ($derivedStatus !== $statusFilter) continue;
        }

        $sid = isset($serviceNonSpMap[$customer['id']]) ? $serviceNonSpMap[$customer['id']] : null;
        $dueDate = null;
        if ($sid && isset($dueMap[$sid])) {
            $dueDate = isset($dueMap[$sid]['Suspension Letter']) ? $dueMap[$sid]['Suspension Letter'] : (isset($dueMap[$sid]['Suspend Letter']) ? $dueMap[$sid]['Suspend Letter'] : (isset($dueMap[$sid]['Expire Letter']) ? $dueMap[$sid]['Expire Letter'] : null));
        }

        if ($fromDate != '') {
            $fDate = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
            if (!$dueDate || $dueDate < $fDate) continue;
        }
        if ($toDate != '') {
            $tDate = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
            if (!$dueDate || $dueDate > $tDate) continue;
        }

        $customer['calculated_due_date'] = $dueDate;
        $filteredCustomers[] = $customer;
    }

    if ($format === 'pdf') {
        if (!class_exists('REPORTPDF')) {
            class REPORTPDF extends TCPDF {
                public function Header() {
                    $image_file = K_PATH_IMAGES . 'aitf.jpg';
                    if (file_exists($image_file)) {
                        $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    }
                    $this->SetFont('helvetica', 'B', 22);
                    $this->SetY(6);
                    $this->SetX(50);
                    $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
                    $this->SetFont('helvetica', 'B', 15);
                    $this->SetX(50);
                    $this->Cell(237, 10, 'CLIENT REPORT', 0, 1, 'C');
                    $this->Ln(2);
                    $this->Line(10, 38, 287, 38);
                }
            }
        }
        $pdf = new REPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetMargins(10, 42, 10);
        $pdf->SetAutoPageBreak(TRUE, 28);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $html = '<table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
                <th width="3%" align="center">Sr.</th>
                <th width="10%" align="center">Unique Id</th>
                <th width="8%" align="center">Agr. Date</th>
                <th width="16%">Client Name</th>
                <th width="14%">Contact Person</th>
                <th width="9%">Status</th>
                <th width="8%" align="center">Due Date</th>
                <th width="6%" align="center">Next Type</th>
                <th width="6%" align="center">Hand</th>
                <th width="20%">Comment</th>
            </tr>
        </thead>
        <tbody>';
        
        $count = 1;
        if (empty($filteredCustomers)) {
            $html .= '<tr><td colspan="10" align="center">No records found</td></tr>';
        } else {
            foreach ($filteredCustomers as $customer) {
                $statusStr = $customer['audit_status'];
                $html .= '<tr>
                    <td align="center" width="3%">' . $count++ . '</td>
                    <td align="center" width="10%">' . htmlspecialchars($customer['unique_id'] ?? '') . '</td>
                    <td align="center" width="8%">' . htmlspecialchars($customer['certification_date'] ?? '') . '</td>
                    <td width="16%"><b>' . htmlspecialchars($customer['customer_name']) . '</b></td>
                    <td width="14%">' . htmlspecialchars($customer['contact_person']) . '</td>
                    <td width="9%">' . htmlspecialchars($statusStr) . '</td>
                    <td align="center" width="8%">' . simpleDateConversion($customer['calculated_due_date']) . '</td>
                    <td align="center" width="6%">' . htmlspecialchars($customer['next_service'] ?? '') . '</td>
                    <td align="center" width="6%">' . htmlspecialchars($handlerMap[$customer['handler']] ?? '') . '</td>
                    <td width="20%">' . htmlspecialchars($customer['comment'] ?? '') . '</td>
                </tr>';
            }
        }
        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $bytes = $pdf->Output('', 'S');
        $attachmentName = 'Home_Report_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatWord = 'PDF';

    } else {
        $xlsx = new SimpleXlsx();
        $xlsx->freezePanesAbove(5);
        $xlsx->setColWidth(1, 6);
        $xlsx->setColWidth(2, 20);
        $xlsx->setColWidth(3, 14);
        $xlsx->setColWidth(4, 30);
        $xlsx->setColWidth(5, 30);
        $xlsx->setColWidth(6, 18);
        $xlsx->setColWidth(7, 14);
        $xlsx->setColWidth(8, 12);
        $xlsx->setColWidth(9, 15);
        $xlsx->setColWidth(10, 35); // Comment

        $filters = '';
        if (!empty($fromDate) || !empty($toDate)) {
            $filters = 'For Period: ' . ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
        }
        if (!empty($clientName)) {
            $filters .= ($filters !== '' ? ', ' : '') . 'Client: ' . strtoupper($clientName);
        }
        if (!empty($serviceType)) {
            $filters .= ($filters !== '' ? ', ' : '') . 'Service: ' . strtoupper($serviceType);
        }
        if (!empty($statusFilter)) {
            $filters .= ($filters !== '' ? ', ' : '') . 'Status: ' . strtoupper($statusFilter);
        }
        if ($filters === '') { $filters = 'All Records'; }

        // Row 1
        $xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
        $xlsx->merge('A1:J1');
        $xlsx->setRowHeight(1, 28);

        // Row 2
        $xlsx->addRow([['v' => 'CLIENT REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
        $xlsx->merge('A2:J2');
        $xlsx->setRowHeight(2, 20);

        // Row 3
        $accentRow = [];
        for ($ac = 0; $ac < 10; $ac++) {
            $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
        }
        $xlsx->addRow($accentRow);
        $xlsx->setRowHeight(3, 8);

        // Row 4
        $headers = ['Sr.', 'Unique Id', 'Agr. Date', 'Client Name', 'Contact Person', 'Status', 'Due Date', 'Next Type', 'Hand', 'Comment'];
        $headerRow = [];
        foreach ($headers as $h) {
            $headerRow[] = ['v' => $h, 's' => 4, 't' => 's'];
        }
        $xlsx->addRow($headerRow);
        $xlsx->setRowHeight(4, 26);

        // Normal row styles
        $sCenter = 7;
        $sClient = 8;
        $sText = 9;

        $count = 1;
        if (empty($filteredCustomers)) {
            $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
            $xlsx->merge('A5:J5');
        } else {
            foreach ($filteredCustomers as $customer) {
                $serviceInfo = isset($serviceMap[$customer['id']]) ? $serviceMap[$customer['id']] : array();
                $statusStr = $customer['audit_status'];
                if ($statusStr == "in-process" && isset($serviceInfo["service_type"])) {
                    $statusStr .= ' (' . $serviceInfo["service_type"] . ')';
                }
                $handlerName = isset($handlerMap[$customer['handler']]) ? $handlerMap[$customer['handler']] : '';

                $row = [];
                $row[] = ['v' => $count++, 's' => $sCenter, 't' => 'n'];
                $row[] = ['v' => $customer['unique_id'] ?? '', 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => $customer['certification_date'] ?? '', 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => $customer['customer_name'], 's' => $sClient, 't' => 's'];
                $row[] = ['v' => $customer['contact_person'], 's' => $sText, 't' => 's'];
                $row[] = ['v' => $statusStr, 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => simpleDateConversion($customer['calculated_due_date']), 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => $customer['next_service'] ?? '', 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => $handlerName, 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => $customer['comment'] ?? '', 's' => $sText, 't' => 's'];
                $xlsx->addRow($row);
            }
        }

        // --- SUMMARY BREAKDOWN ---
        $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]); // spacer

        $statusCounts = [];
        foreach ($filteredCustomers as $customer) {
            $st = hpDerivedStatus($customer, $serviceMap);
            $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;
        }

        $curRow = 4 + (empty($filteredCustomers) ? 1 : count($filteredCustomers)) + 1;

        $curRow++;
        $xlsx->addRow([['v' => 'CLIENT REPORT SUMMARY', 's' => 16, 't' => 's']]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);

        $curRow++;
        $xlsx->addRow([
            ['v' => 'TOTAL CLIENTS', 's' => 18, 't' => 's'],
            null, null,
            ['v' => count($filteredCustomers), 's' => 20, 't' => 'n'],
        ]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);

        if (!empty($statusCounts)) {
            $curRow++;
            $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]); // spacer

            $curRow++;
            $xlsx->addRow([['v' => 'STATUS BREAKDOWN', 's' => 16, 't' => 's']]);
            $xlsx->merge('A' . $curRow . ':C' . $curRow);

            $curRow++;
            $xlsx->addRow([
                ['v' => 'Status', 's' => 22, 't' => 's'],
                null,
                ['v' => 'No. of Clients', 's' => 22, 't' => 's'],
            ]);
            $xlsx->merge('A' . $curRow . ':B' . $curRow);

            arsort($statusCounts);
            foreach ($statusCounts as $st => $cnt) {
                $curRow++;
                $xlsx->addRow([
                    ['v' => strtoupper($st), 's' => 24, 't' => 's'],
                    null,
                    ['v' => $cnt, 's' => 20, 't' => 'n'],
                ]);
                $xlsx->merge('A' . $curRow . ':B' . $curRow);
            }
        }

        $bytes = $xlsx->toString('Client Report');
        $attachmentName = 'Home_Report_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatWord = 'Excel';
    }
} catch (Exception $e) {
    respond(false, 'Could not generate the report. Please try again.');
}

try {
    aitfLoadPHPMailer();
    $mailer = new PHPMailer();
    $mailer->isHTML(true);
    $mailer->Subject = 'Home Client Report (' . $formatWord . ')';
    $mailer->Body = '<p>Please find the requested Home Client Report attached in ' . $formatWord . ' format.</p>';
    $mailer->AltBody = 'Please find the requested Home Client Report attached.';
    
    $added = aitfAddEmailRecipients($mailer, $email);
    if ($added === 0) {
        respond(false, 'Please enter a valid email address.');
    }
    
    $mailer->addStringAttachment($bytes, $attachmentName, 'base64', $mimeType);
    list($mailSent, $mailError) = aitfSendInvoiceMailer($mailer);
    
    if ($mailSent) {
        respond(true, 'The ' . $formatWord . ' report has been sent to ' . $email . '.');
    }
    respond(false, 'Could not send the email right now. Please try again later.');
} catch (Exception $e) {
    respond(false, 'Could not send the email. Please try again later.');
}

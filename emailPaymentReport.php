<?php
header('Content-Type: application/json');
include_once './dbConnect.php';
include_once './commonutil.php';
require_once('tcpdf_include.php');
include_once __DIR__ . '/include/invoice_mail_helper.php';
include_once __DIR__ . '/include/invoice_report_lib.php';

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$email = $_POST['email'] ?? '';
$format = $_POST['format'] ?? 'pdf';
if (empty($email)) {
    respond(false, 'Email address is required.');
}

$minAmount = $_POST['minAmount'] ?? '';
$maxAmount = $_POST['maxAmount'] ?? '';
$clientName = $_POST['clientName'] ?? '';

// Helper functions
function formatIndian($num) {
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
    return $whole . "." . $decimal;
}

function convertToWord($number) {
    $no = (int) floor($number);
    $point = (int) round(($number - $no) * 100);
    if ($point === 100) { $no = $no + 1; $point = 0; }
    $hundred = null;
    $digits_1 = strlen((string) $no);
    $i = 0; $str = array();
    $words = array('0' => '', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
        '7' => 'Seven', '8' => 'Eight', '9' => 'Nine', '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
        '13' => 'Thirteen', '14' => 'Fourteen', '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
        '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty', '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
        '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty', '90' => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    $n = $no;
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($n % $divider);
        $n = floor($n / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred :
                    $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
        } else $str[] = null;
    }
    $str = array_reverse($str);
    $result = trim(implode('', $str));
    if ($result === '') { $result = 'Zero'; }
    $pointsWords = '';
    if ($point) {
        $pointsWords = ($point < 21) ? $words[$point] : ($words[floor($point / 10) * 10] . " " . $words[$point % 10]);
        $pointsWords = trim($pointsWords);
    }
    return $result . " Rupees" . ($pointsWords !== '' ? (" and " . $pointsWords . " Paise") : "") . " Only";
}

$sql = "SELECT ci.id, customer_name, unique_id, contact_person, contact, ps.payment_id, ps.service_type, balance, ps.comment 
        FROM customer_info ci 
        JOIN payment_system ps on ps.customer_id = ci.id 
        WHERE ps.balance > 0 AND ps.service_type <> 'TDS' AND ci.is_deleted = 'NO'";

$params = [];
if ($clientName != '') {
    $sql .= " AND ci.customer_name LIKE :clientName";
    $params[':clientName'] = '%' . $clientName . '%';
}
if ($minAmount != '') {
    $sql .= " AND ps.balance >= :minAmount";
    $params[':minAmount'] = (float)$minAmount;
}
if ($maxAmount != '') {
    $sql .= " AND ps.balance <= :maxAmount";
    $params[':maxAmount'] = (float)$maxAmount;
}
$sql .= " ORDER BY ps.balance DESC";

$statement = $db->prepare($sql);
foreach ($params as $key => &$val) { $statement->bindParam($key, $val); }
$statement->execute();
$payments = $statement->fetchAll(PDO::FETCH_ASSOC);

try {
    if ($format === 'pdf') {
        class REPORTPDF extends TCPDF {
            public function Header() {
                $image_file = K_PATH_IMAGES . 'aitf.jpg';
                if (file_exists($image_file)) {
                    $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                }
                
                $this->SetFont('helvetica', 'B', 22);
                $this->SetTextColor(0, 0, 0);
                $this->SetY(6);
                $this->SetX(50);
                $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
                
                $hasFilters = !empty($_POST['minAmount']) || !empty($_POST['maxAmount']) || !empty($_POST['clientName']);
                $mainTitle = $hasFilters ? 'PAYMENT REPORT (FILTERED)' : 'PAYMENT REPORT (ALL)';
                
                $this->SetFont('helvetica', 'B', 15);
                $this->SetX(50);
                $this->Cell(237, 10, $mainTitle, 0, 1, 'C');
                
                $filters = "";
                if(!empty($_POST['minAmount']) || !empty($_POST['maxAmount'])) {
                    $filters .= "AMOUNT RANGE: " . ($_POST['minAmount'] ?? '0') . " TO " . ($_POST['maxAmount'] ?? 'ANY') . "    ";
                }
                if(!empty($_POST['clientName'])) {
                    $filters .= "CLIENT: " . strtoupper($_POST['clientName']);
                }
                if($filters) {
                    $this->SetFont('helvetica', 'B', 10);
                    $this->SetX(50);
                    $this->Cell(237, 6, $filters, 0, 1, 'C');
                }
                
                $this->Ln(2);
                $this->Line(10, 38, 287, 38);
            }
            
            public function Footer() {
                $this->SetY(-25);
                $this->SetFont('helvetica', 'B', 9);
                $this->SetTextColor(100, 100, 100);
                $this->Cell(0, 5, 'NOTE: THIS IS A SYSTEM GENERATED DOCUMENT AND DOES NOT REQUIRE A PHYSICAL SIGNATURE.', 0, 1, 'C');
                $this->SetFont('helvetica', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }

        $pdf = new REPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetMargins(10, 42, 10);
        $pdf->SetAutoPageBreak(TRUE, 28);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 9);

        $html = '<table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
                <th width="5%" align="center">Sr.</th>
                <th width="25%">Client Name</th>
                <th width="15%">Contact Person</th>
                <th width="15%">Phone</th>
                <th width="15%">Service Type</th>
                <th width="25%" align="right">Pending Amount</th>
            </tr>
        </thead>
        <tbody>';

        $totalPending = 0;
        $count = 1;
        if (empty($payments)) {
            $html .= '<tr><td colspan="6" align="center">No records found</td></tr>';
        } else {
            foreach ($payments as $pay) {
                $totalPending += (float)$pay['balance'];

                $html .= '<tr>
                    <td align="center" width="5%">' . $count++ . '</td>
                    <td width="25%"><b>' . htmlspecialchars($pay['customer_name']) . '</b></td>
                    <td width="15%">' . htmlspecialchars($pay['contact_person']) . '</td>
                    <td width="15%">' . htmlspecialchars($pay['contact']) . '</td>
                    <td width="15%">' . htmlspecialchars($pay['service_type']) . '</td>
                    <td align="right" width="25%"><b>' . formatIndian($pay['balance']) . '/-</b></td>
                </tr>';
            }
        }
        $html .= '</tbody></table>';

        $html .= '<br><br><table border="0" cellpadding="0" cellspacing="0"><tr><td style="border-bottom: 2px solid #0d4d8b;"></td></tr></table><br>';

        $html .= '<br><br>
        <div nobr="true">
            <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
                <tr style="background-color: #0d4d8b; color: #ffffff;">
                    <th colspan="2" align="center" style="font-weight: bold; font-size: 12px;">PAYMENT REPORT SUMMARY</th>
                </tr>
                <tr>
                    <td width="50%"><b>TOTAL PENDING CASES</b></td>
                    <td width="50%" align="right"><b>' . count($payments) . '</b></td>
                </tr>
                <tr>
                    <td width="50%"><b>TOTAL PENDING AMOUNT</b></td>
                    <td width="50%" align="right" style="color: #0d4d8b; font-size: 12px;"><b>Rs. ' . formatIndian($totalPending) . '/-</b></td>
                </tr>
                <tr>
                    <td width="100%" colspan="2" style="font-style: italic;"><b>AMOUNT IN WORDS:</b> ' . strtoupper(convertToWord($totalPending)) . '</td>
                </tr>
            </table>
        </div>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $bytes = $pdf->Output('Payment_Pending_Report.pdf', 'S');
        $attachmentName = 'Payment_Pending_Report_' . date('Ymd_His') . '.pdf';
        $mimeType = 'application/pdf';
        $formatWord = 'PDF';

    } else {
        // EXCEL Generation
        $xlsx = new SimpleXlsx();
        $xlsx->setColWidth(1, 6);
        $xlsx->setColWidth(2, 35);
        $xlsx->setColWidth(3, 25);
        $xlsx->setColWidth(4, 20);
        $xlsx->setColWidth(5, 20);
        $xlsx->setColWidth(6, 20);

        $filters = '';
        if (!empty($minAmount) || !empty($maxAmount)) {
            $filters .= "AMOUNT RANGE: " . ($minAmount !== '' ? $minAmount : '0') . " TO " . ($maxAmount !== '' ? $maxAmount : 'ANY') . "    ";
        }
        if (!empty($clientName)) { $filters .= "CLIENT: " . strtoupper($clientName); }
        if ($filters === '') { $filters = 'All Records'; }

        $xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
        $xlsx->merge('A1:F1');
        $xlsx->setRowHeight(1, 28);

        $xlsx->addRow([['v' => 'PAYMENT REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
        $xlsx->merge('A2:F2');
        $xlsx->setRowHeight(2, 20);

        $accentRow = [];
        for ($ac = 0; $ac < 6; $ac++) {
            $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
        }
        $xlsx->addRow($accentRow);
        $xlsx->setRowHeight(3, 8);

        $headers = ['Sr.', 'Client Name', 'Contact Person', 'Phone', 'Service Type', 'Pending Amount'];
        $headerRow = [];
        foreach ($headers as $h) { $headerRow[] = ['v' => $h, 's' => 4, 't' => 's']; }
        $xlsx->addRow($headerRow);
        $xlsx->setRowHeight(4, 26);

        $sCenter = 7; $sClient = 8; $sText = 9; $sAmount = 19;
        $count = 1;
        $totalPending = 0;

        if (empty($payments)) {
            $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
            $xlsx->merge('A5:F5');
        } else {
            foreach ($payments as $pay) {
                $totalPending += (float)$pay['balance'];
                
                $row = [];
                $row[] = ['v' => $count++, 's' => $sCenter, 't' => 'n'];
                $row[] = ['v' => $pay['customer_name'], 's' => $sClient, 't' => 's'];
                $row[] = ['v' => $pay['contact_person'], 's' => $sText, 't' => 's'];
                $row[] = ['v' => $pay['contact'], 's' => $sText, 't' => 's'];
                $row[] = ['v' => $pay['service_type'], 's' => $sCenter, 't' => 's'];
                $row[] = ['v' => round($pay['balance'], 2), 's' => $sAmount, 't' => 'n'];
                $xlsx->addRow($row);
            }
        }

        // Summary
        $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]);
        $curRow = 4 + (empty($payments) ? 1 : count($payments)) + 1;
        $curRow++;
        $xlsx->addRow([['v' => 'PAYMENT REPORT SUMMARY', 's' => 16, 't' => 's']]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);

        $curRow++;
        $xlsx->addRow([['v' => 'TOTAL PENDING CASES', 's' => 18, 't' => 's'], null, null, ['v' => count($payments), 's' => 20, 't' => 'n']]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);

        $curRow++;
        $xlsx->addRow([['v' => 'TOTAL PENDING AMOUNT', 's' => 18, 't' => 's'], null, null, ['v' => round($totalPending, 2), 's' => 19, 't' => 'n']]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);

        $bytes = $xlsx->toString('Payment Report');
        $attachmentName = 'Payment_Pending_Report_' . date('Ymd_His') . '.xlsx';
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $formatWord = 'Excel';
    }

    $subject = "Payment Report - " . date('d M Y');
    $body = "<h2>AITF Management Certification Limited</h2>
             <p>Hello,</p>
             <p>Please find attached the requested Payment Report in <strong>{$formatWord}</strong> format.</p>
             <p><b>Filter Criteria:</b><br>";
    if (!empty($minAmount) || !empty($maxAmount)) {
        $body .= "Amount Range: " . ($minAmount ?: '0') . " to " . ($maxAmount ?: 'ANY') . "<br>";
    }
    if (!empty($clientName)) { $body .= "Client: " . htmlspecialchars($clientName) . "<br>"; }
    if (empty($minAmount) && empty($maxAmount) && empty($clientName)) {
        $body .= "All Records<br>";
    }
    $body .= "</p><p>Regards,<br>AITF System</p>";

    $sent = sendInvoiceMail($email, $subject, $body, $attachmentName, $bytes, $mimeType);
    if ($sent) {
        respond(true, 'Report sent to ' . htmlspecialchars($email));
    } else {
        respond(false, 'Failed to send the email. Please try again.');
    }

} catch (Exception $e) {
    respond(false, 'An error occurred: ' . $e->getMessage());
}

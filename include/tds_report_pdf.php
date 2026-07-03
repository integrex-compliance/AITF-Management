<?php
require_once __DIR__ . '/../tcpdf_include.php';
require_once __DIR__ . '/tds_report_lib.php';

if (!class_exists('TDSREPORTPDF')) {
class TDSREPORTPDF extends TCPDF {
    public $reportTitle = 'TDS REPORT';
    public $filters = '';

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

        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $this->Cell(237, 10, $this->reportTitle, 0, 1, 'C');

        if ($this->filters) {
            $this->SetFont('helvetica', 'B', 10);
            $this->SetX(50);
            $this->Cell(237, 6, $this->filters, 0, 1, 'C');
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
}

function buildTdsReportPdfBytes($data, $status, $startDateStr, $endDateStr, $clientSearch, $sortOrder) {
    $tdsList = $data['tdsList'];

    $pdf = new TDSREPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->reportTitle = tdsReportTitle($status);
    $pdf->filters = tdsReportFilterText($status, $startDateStr, $endDateStr, $clientSearch);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetMargins(10, 42, 10);
    $pdf->SetAutoPageBreak(TRUE, 28);
    if (method_exists($pdf, 'setFontSubsetting')) {
        $pdf->setFontSubsetting(false);
    }
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    $html = '<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
            <th width="5%" align="center">Sr.</th>
            <th width="12%">Unique ID</th>
            <th width="24%">Client Name</th>
            <th width="16%">Service Type</th>
            <th width="13%" align="right">TDS Deducted</th>
            <th width="13%" align="right">Payment Received</th>
            <th width="9%" align="center">Date</th>
            <th width="8%" align="center">Status</th>
        </tr>
    </thead>
    <tbody>';

    $totalDeducted = 0;
    $totalReceived = 0;
    $receivedCount = 0;
    $pendingCount = 0;
    $count = 1;

    if (empty($tdsList)) {
        $html .= '<tr><td colspan="8" align="center">No records found</td></tr>';
    } else {
        foreach ($tdsList as $tds) {
            $deducted = tdsDeductedAmount($tds);
            $received = tdsReceivedAmount($tds);
            $totalDeducted += $deducted;
            $totalReceived += $received;
            $isReceived = $received > 0;
            if ($isReceived) {
                $receivedCount++;
                $rowStyle = 'background-color:#dcfce7; color:#166534;';
                $statusText = 'RECEIVED';
            } else {
                $pendingCount++;
                $rowStyle = 'background-color:#fee2e2; color:#991b1b;';
                $statusText = 'PENDING';
            }

            $html .= '<tr style="' . $rowStyle . '">
                <td align="center" width="5%">' . $count++ . '</td>
                <td width="12%">' . htmlspecialchars($tds['unique_id']) . '</td>
                <td width="24%"><b>' . htmlspecialchars($tds['customer_name']) . '</b></td>
                <td width="16%">' . htmlspecialchars($tds['tds']) . '</td>
                <td align="right" width="13%">' . formatIndian($deducted) . '</td>
                <td align="right" width="13%">' . formatIndian($received) . '</td>
                <td align="center" width="9%">' . tdsReportDisplayDate($tds['payment_date']) . '</td>
                <td align="center" width="8%"><b>' . $statusText . '</b></td>
            </tr>';
        }
    }
    $html .= '</tbody></table>';

    $pendingAmount = $totalDeducted - $totalReceived;
    $html .= '<br><br>
    <div nobr="true">
        <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
            <tr style="background-color: #0d4d8b; color: #ffffff;">
                <th colspan="2" align="center" style="font-weight: bold; font-size: 12px;">TDS REPORT SUMMARY</th>
            </tr>
            <tr><td width="50%"><b>TOTAL CASES</b></td><td width="50%" align="right"><b>' . count($tdsList) . '</b></td></tr>
            <tr><td width="50%"><b>TOTAL TDS DEDUCTED</b></td><td width="50%" align="right" style="color: #0d4d8b; font-size: 12px;"><b>Rs. ' . formatIndian($totalDeducted) . '/-</b></td></tr>
            <tr><td width="50%"><b>TOTAL PAYMENT RECEIVED</b></td><td width="50%" align="right" style="color: #0d4d8b; font-size: 12px;"><b>Rs. ' . formatIndian($totalReceived) . '/-</b></td></tr>
            <tr><td width="50%" style="background-color:#0d4d8b; color:#ffffff;"><b>TOTAL PENDING AMOUNT</b></td><td width="50%" align="right" style="background-color:#0d4d8b; color:#ffffff;"><b>Rs. ' . formatIndian($pendingAmount) . '/-</b></td></tr>
            <tr><td width="50%" style="background-color:#dcfce7; color:#166534;"><b>RECEIVED RECORDS</b></td><td width="50%" align="right"><b>' . $receivedCount . '</b></td></tr>
            <tr><td width="50%" style="background-color:#fee2e2; color:#991b1b;"><b>PENDING RECORDS</b></td><td width="50%" align="right"><b>' . $pendingCount . '</b></td></tr>
            <tr><td width="100%" colspan="2" style="font-style: italic;"><b>PENDING AMOUNT IN WORDS:</b> ' . strtoupper(convertToWord($pendingAmount)) . '</td></tr>
        </table>
    </div>';

    $pdf->writeHTML($html, true, false, true, false, '');
    return $pdf->Output('TDS_Report.pdf', 'S');
}

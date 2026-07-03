<?php
require_once __DIR__ . '/../tcpdf_include.php';
require_once __DIR__ . '/agreement_report_lib.php';

if (!class_exists('AGREEMENTREPORTPDF')) {
class AGREEMENTREPORTPDF extends TCPDF {
    public $reportFromDate = '';
    public $reportToDate = '';
    public $reportClientName = '';

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
        $this->Cell(237, 10, 'CERTIFICATION AGREEMENT REPORT', 0, 1, 'C');

        $filters = "";
        if ($this->reportFromDate !== '' || $this->reportToDate !== '') {
            $filters .= "PERIOD: " . ($this->reportFromDate !== '' ? $this->reportFromDate : 'Start')
                . " TO " . ($this->reportToDate !== '' ? $this->reportToDate : 'End') . "    ";
        }
        if ($this->reportClientName !== '') {
            $filters .= "CLIENT: " . strtoupper($this->reportClientName);
        }
        if ($filters) {
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
}

function buildAgreementReportPdfBytes($data, $fromDate, $toDate, $clientName) {
    $agreements = $data['agreements'];

    $pdf = new AGREEMENTREPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->reportFromDate = $fromDate;
    $pdf->reportToDate = $toDate;
    $pdf->reportClientName = $clientName;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle('Agreement Report');
    $pdf->SetMargins(10, 42, 10);
    $pdf->SetAutoPageBreak(TRUE, 28);
    if (method_exists($pdf, 'setFontSubsetting')) {
        $pdf->setFontSubsetting(false);
    }
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    $html = '<table border="1" cellpadding="6">
    <thead>
        <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
            <th width="5%" align="center">Sr.</th>
            <th width="24%">Client Name</th>
            <th width="18%">Service Type</th>
            <th width="16%">Agreement No.</th>
            <th width="11%" align="center">Date</th>
            <th width="12%" align="right">Amount</th>
            <th width="14%">Status</th>
        </tr>
    </thead>
    <tbody>';

    $totalAmount = 0;
    $greenCount = 0;
    $redCount = 0;
    $otherCount = 0;
    $count = 1;

    if (empty($agreements)) {
        $html .= '<tr><td colspan="7" align="center">No records found</td></tr>';
    } else {
        foreach ($agreements as $agreement) {
            $amount = agreementReportAmount($agreement);
            $totalAmount += $amount;
            $bucket = agreementReportStatusBucket($agreement['audit_status']);

            if ($bucket === 'green') {
                $greenCount++;
                $rowStyle = 'background-color:#dcfce7; color:#166534;';
            } elseif ($bucket === 'red') {
                $redCount++;
                $rowStyle = 'background-color:#fee2e2; color:#991b1b;';
            } else {
                $otherCount++;
                $rowStyle = 'background-color:#eaf3ff; color:#0d4d8b;';
            }

            $html .= '<tr style="' . $rowStyle . '">
                <td align="center" width="5%">' . $count++ . '</td>
                <td width="24%"><b>' . htmlspecialchars($agreement['customer_name']) . '</b></td>
                <td width="18%">' . htmlspecialchars($agreement['service_type']) . '</td>
                <td width="16%">' . htmlspecialchars($agreement['aggrement_no']) . '</td>
                <td align="center" width="11%">' . agreementReportDisplayDate($agreement['aggrement_date']) . '</td>
                <td align="right" width="12%"><b>' . formatIndian($amount) . '/-</b></td>
                <td width="14%"><b>' . htmlspecialchars($agreement['audit_status']) . '</b></td>
            </tr>';
        }
    }

    $html .= '</tbody></table>';

    $html .= '<br><br>
    <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
        <tr>
            <th colspan="2" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">CERTIFICATION AGREEMENT REPORT SUMMARY</th>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold;">TOTAL NUMBER OF AGREEMENTS</td>
            <td width="50%" align="right" style="font-weight: bold;">' . count($agreements) . '</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#0d4d8b; color:#ffffff;">TOTAL AGREEMENT AMOUNT</td>
            <td width="50%" align="right" style="font-weight: bold; background-color:#0d4d8b; color:#ffffff;">Rs. ' . formatIndian($totalAmount) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#dcfce7; color:#166534;">GREEN STATUS RECORDS</td>
            <td width="50%" align="right" style="font-weight: bold;">' . $greenCount . '</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#fee2e2; color:#991b1b;">RED STATUS RECORDS</td>
            <td width="50%" align="right" style="font-weight: bold;">' . $redCount . '</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#eaf3ff; color:#0d4d8b;">OTHER STATUS RECORDS</td>
            <td width="50%" align="right" style="font-weight: bold;">' . $otherCount . '</td>
        </tr>
        <tr>
            <td width="100%" colspan="2" style="font-style: italic;"><b>AMOUNT IN WORDS:</b> ' . strtoupper(convertToWord($totalAmount)) . '</td>
        </tr>
    </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    return $pdf->Output('Agreement_Report.pdf', 'S');
}

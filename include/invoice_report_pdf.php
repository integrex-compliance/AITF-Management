<?php
/**
 * include/invoice_report_pdf.php
 *
 * PDF builder for the Invoice Report. Kept separate from invoice_report_lib.php
 * so the Excel path never has to load TCPDF.
 *
 *   buildInvoiceReportPdfBytes($data, $type, $from, $to, $client) -> raw PDF bytes
 *
 * Requires: tcpdf_include.php, commonutil.php (simpleDateConversion),
 *           invoice_report_lib.php (cleanAmount / taxAmount / formatIndian / convertToWord)
 */
require_once __DIR__ . '/../tcpdf_include.php';
require_once __DIR__ . '/invoice_report_lib.php';

if (!class_exists('REPORTPDF')) {
class REPORTPDF extends TCPDF {
    // Filter context for the header (set from the builder so we don't rely on $_POST).
    public $reportType = 'proforma';
    public $reportFromDate = '';
    public $reportToDate = '';
    public $reportClientName = '';

    public function Header() {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 6, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Main Heading
        $this->SetFont('helvetica', 'B', 22);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(6);
        $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');

        // Subtitle
        $this->SetFont('helvetica', 'B', 15);
        $this->SetX(50);
        $type = ($this->reportType == 'proforma') ? 'PROFORMA' : (($this->reportType == 'pending') ? 'PENDING & FOLLOW UP' : 'FINAL');
        $this->Cell(237, 10, $type . ' INVOICE REPORT', 0, 1, 'C');

        // Filters metadata
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

/**
 * Build the invoice report PDF and return it as raw bytes (dest 'S').
 */
function buildInvoiceReportPdfBytes($data, $invoiceType, $fromDate, $toDate, $clientName) {
    $invoices = $data['invoices'];
    $svcMap   = $data['svcMap'];

    $GLOBALS['invoiceExportStage'] = 'initializing TCPDF';
    invoiceReportLog('pdf.tcpdf.init.start', ['invoice_count' => count($invoices)]);
    $pdf = new REPORTPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->reportType       = $invoiceType;
    $pdf->reportFromDate   = $fromDate;
    $pdf->reportToDate     = $toDate;
    $pdf->reportClientName = $clientName;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle('Invoice Report');
    $pdf->SetMargins(10, 42, 10);
    $pdf->SetAutoPageBreak(TRUE, 28);
    // Core font (helvetica) — skip font subsetting to keep generation fast.
    if (method_exists($pdf, 'setFontSubsetting')) {
        $pdf->setFontSubsetting(false);
    }
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);
    invoiceReportLog('pdf.tcpdf.init.done');

    $GLOBALS['invoiceExportStage'] = 'generating PDF HTML';
    invoiceReportLog('pdf.html.start', ['invoice_count' => count($invoices)]);
    $html = '<table border="1" cellpadding="6">
    <thead>
        <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b;">
            <th width="4%" align="center">Sr.</th>
            <th width="13%">Client Name</th>
            <th width="11%">GSTIN No</th>
            <th width="11%">Invoice No.</th>
            <th width="7%" align="center">Date</th>
            <th width="17%">Particulars</th>
            <th width="8%" align="right">Amount</th>
            <th width="7%" align="right">CGST@9%</th>
            <th width="7%" align="right">SGST@9%</th>
            <th width="7%" align="right">IGST@18%</th>
            <th width="8%" align="center">Payment Status</th>
        </tr>
    </thead>
    <tbody>';

    $totalSum = 0;
    $totalCgst = 0;
    $totalSgst = 0;
    $totalIgst = 0;
    $paidCount = 0;
    $unpaidCount = 0;
    $paidAmount = 0;
    $count = 1;
    if (empty($invoices)) {
        $html .= '<tr><td colspan="11" align="center">No records found</td></tr>';
    } else {
        foreach ($invoices as $inv) {
            $invoiceNo = ($invoiceType == 'proforma')
                ? $inv['profarma_prefix'] . '-' . $inv['invoice_no']
                : $inv['final_prefix'] . '-' . $inv['final_invoice_no'];

            $particularsArray = isset($svcMap[$inv['invoice_id']]) ? $svcMap[$inv['invoice_id']] : [];
            $particulars = implode(', ', $particularsArray);

            $amount = cleanAmount($inv['final_amount']);
            $totalSum += $amount;

            $cgstVal = taxAmount($inv['cgst']);
            $sgstVal = taxAmount($inv['sgst']);
            $igstVal = taxAmount($inv['igst']);

            if ($cgstVal !== null) $totalCgst += $cgstVal;
            if ($sgstVal !== null) $totalSgst += $sgstVal;
            if ($igstVal !== null) $totalIgst += $igstVal;

            $cgstCell = ($cgstVal !== null) ? formatIndian($cgstVal) . '/-' : '-';
            $sgstCell = ($sgstVal !== null) ? formatIndian($sgstVal) . '/-' : '-';
            $igstCell = ($igstVal !== null) ? formatIndian($igstVal) . '/-' : '-';

            $isPaid = (isset($inv['invoice_status']) && $inv['invoice_status'] == 'Rcv');
            if ($isPaid) {
                $paidCount++;
                $paidAmount += $amount;
                $rowStyle = 'background-color:#dbeafe; color:#0d4d8b;';
                $statusCell = '<b>PAID</b>';
            } else {
                $unpaidCount++;
                $rowStyle = '';
                $statusCell = '<span style="color:#cc0000;">UNPAID</span>';
            }

            $html .= '<tr style="' . $rowStyle . '">
                <td align="center" width="4%">' . $count++ . '</td>
                <td width="13%"><b>' . htmlspecialchars($inv['customer_name']) . '</b></td>
                <td width="11%">' . htmlspecialchars($inv['gstin_no']) . '</td>
                <td width="11%">' . htmlspecialchars($invoiceNo) . '</td>
                <td align="center" width="7%">' . simpleDateConversion($inv['invoice_date']) . '</td>
                <td width="17%">' . htmlspecialchars($particulars) . '</td>
                <td align="right" width="8%"><b>' . formatIndian($amount) . '/-</b></td>
                <td align="right" width="7%">' . $cgstCell . '</td>
                <td align="right" width="7%">' . $sgstCell . '</td>
                <td align="right" width="7%">' . $igstCell . '</td>
                <td align="center" width="8%">' . $statusCell . '</td>
            </tr>';
        }
    }

    $html .= '</tbody></table>';

    // REPORT SUMMARY SECTION
    $invoiceCount = count($invoices);
    $summaryTitle = strtoupper($invoiceType) . ' INVOICE REPORT SUMMARY';
    $totalTaxSum = $totalCgst + $totalSgst + $totalIgst;
    $grandTotal = $totalSum + $totalTaxSum;
    $amountInWords = convertToWord($grandTotal);

    $html .= '<br><br>
    <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
        <tr>
            <th colspan="2" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">' . $summaryTitle . '</th>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold;">TOTAL NUMBER OF INVOICES</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">' . $invoiceCount . '</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold;">TOTAL INVOICE AMOUNT (TAXABLE)</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 12px; color: #0d4d8b;">Rs. ' . formatIndian($totalSum) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#eaf3ff;">TOTAL CGST @9%</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 11px; background-color:#eaf3ff; color:#0d4d8b;">Rs. ' . formatIndian($totalCgst) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#eaf3ff;">TOTAL SGST @9%</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 11px; background-color:#eaf3ff; color:#0d4d8b;">Rs. ' . formatIndian($totalSgst) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#eaf3ff;">TOTAL IGST @18%</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 11px; background-color:#eaf3ff; color:#0d4d8b;">Rs. ' . formatIndian($totalIgst) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold;">TOTAL TAX AMOUNT</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 11px;">Rs. ' . formatIndian($totalTaxSum) . '/-</td>
        </tr>
        <tr>
            <td width="50%" style="font-weight: bold; background-color:#0d4d8b; color:#ffffff;">GRAND TOTAL (INCL. TAX)</td>
            <td width="50%" align="right" style="font-weight: bold; font-size: 13px; background-color:#0d4d8b; color:#ffffff;">Rs. ' . formatIndian($grandTotal) . '/-</td>
        </tr>
        <tr>
            <td width="100%" colspan="2" style="font-style: italic;"><b>AMOUNT IN WORDS:</b> ' . strtoupper($amountInWords) . '</td>
        </tr>
    </table>';

    // PAYMENT STATUS SUMMARY SECTION
    $unpaidAmount = $totalSum - $paidAmount;
    $html .= '<br>
    <table border="1" cellpadding="8" style="background-color: #fcfcfc;">
        <tr>
            <th colspan="3" align="center" style="background-color: #0d4d8b; color: #ffffff; font-weight: bold; font-size: 12px;">PAYMENT STATUS SUMMARY</th>
        </tr>
        <tr style="background-color:#eaf3ff; font-weight:bold; color:#0d4d8b; text-align:center;">
            <th width="34%" align="center">Status</th>
            <th width="33%" align="center">No. of Invoices</th>
            <th width="33%" align="center">Invoice Amount</th>
        </tr>
        <tr style="background-color:#dbeafe; color:#0d4d8b;">
            <td width="34%" align="center" style="font-weight: bold;">PAID</td>
            <td width="33%" align="center" style="font-weight: bold;">' . $paidCount . '</td>
            <td width="33%" align="right" style="font-weight: bold;">Rs. ' . formatIndian($paidAmount) . '/-</td>
        </tr>
        <tr>
            <td width="34%" align="center" style="font-weight: bold; color:#cc0000;">UNPAID</td>
            <td width="33%" align="center" style="font-weight: bold;">' . $unpaidCount . '</td>
            <td width="33%" align="right" style="font-weight: bold;">Rs. ' . formatIndian($unpaidAmount) . '/-</td>
        </tr>
    </table>';
    invoiceReportLog('pdf.html.done', ['html_bytes' => strlen($html)]);

    $GLOBALS['invoiceExportStage'] = 'rendering PDF HTML with TCPDF';
    invoiceReportLog('pdf.write_html.start', ['html_bytes' => strlen($html)]);
    $pdf->writeHTML($html, true, false, true, false, '');
    invoiceReportLog('pdf.write_html.done', ['page_count' => $pdf->getNumPages()]);

    $GLOBALS['invoiceExportStage'] = 'creating PDF output string';
    invoiceReportLog('pdf.output_string.start', ['page_count' => $pdf->getNumPages()]);
    $bytes = $pdf->Output('Invoice_Report.pdf', 'S');
    invoiceReportLog('pdf.output_string.done', ['bytes' => strlen($bytes)]);
    return $bytes;
}

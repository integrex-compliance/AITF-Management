<?php
require_once __DIR__ . '/../tcpdf_include.php';

function aitf_payment_report_amount($value) {
    $parts = explode('.', number_format((float)$value, 2, '.', ''));
    $parts[0] = preg_replace('/\B(?=(\d{2})*\d{3}(?!\d))/', ',', $parts[0]);
    return implode('.', $parts);
}

function aitf_payment_report_totals(array $payments) {
    $totals = array_fill_keys(['total_inv', 'total_paid', 'total_tds', 'total_disc', 'balance'], 0);
    $clients = [];
    $invoices = 0;
    foreach ($payments as $payment) {
        $clients[$payment['id']] = true;
        $invoices += count($payment['invoices']);
        foreach ($totals as $key => $value) $totals[$key] += (int)round((float)$payment[$key] * 100);
    }
    foreach ($totals as &$value) $value /= 100;
    unset($value);
    return $totals + ['clients' => count($clients), 'headers' => count($payments), 'invoices' => $invoices];
}

function aitf_payment_report_html(array $payments, array $filters = []) {
    $esc = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
    $totals = aitf_payment_report_totals($payments);
    $filtered = ($filters['clientName'] ?? '') !== '' || ($filters['minAmount'] ?? '') !== '' || ($filters['maxAmount'] ?? '') !== '';
    $html = '<p><b>' . ($filtered ? 'Total Due Pending Amount (filtered)' : 'Total Due Pending Amount') . ': Rs. ' . aitf_payment_report_amount($totals['balance']) . '</b><br><span style="color:#64748b;">Amounts in INR. Each row represents one pending invoice header.</span></p>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0"><thead><tr style="background-color:#0f2744;color:#ffffff;font-weight:bold;">'
        . '<th width="4%" align="center">Sr.</th><th width="20%">Client Name</th><th width="13%">Invoice Header</th>'
        . '<th width="18%">Invoices Issued</th><th width="12%" align="right">Amount Received</th>'
        . '<th width="12%" align="right">Amount Pending</th><th width="21%">Comments (If any)</th></tr></thead><tbody>';
    if (!$payments) $html .= '<tr><td colspan="7" align="center">No pending payments match the selected filters.</td></tr>';
    foreach ($payments as $index => $payment) {
        $client = '<b>' . $esc($payment['customer_name']) . '</b>';
        $contact = trim((string)($payment['contact_person'] ?? ''));
        if ($contact !== '') $client .= '<br>' . $esc($contact);
        $numbers = [];
        foreach ($payment['invoices'] as $invoice) $numbers[] = $esc($invoice['number']);
        $html .= '<tr style="background-color:' . ($index % 2 ? '#f8fafc' : '#ffffff') . ';">'
            . '<td width="4%" align="center">' . ($index + 1) . '</td>'
            . '<td width="20%">' . $client . '</td>'
            . '<td width="13%">' . $esc($payment['service_type']) . '</td>'
            . '<td width="18%">' . implode('<br>', $numbers) . '</td>'
            . '<td width="12%" align="right">' . aitf_payment_report_amount($payment['total_paid']) . '</td>'
            . '<td width="12%" align="right"><b>' . aitf_payment_report_amount($payment['balance']) . '</b></td>'
            . '<td width="21%">' . nl2br($esc($payment['comment'] ?? '')) . '</td></tr>';
    }
    $html .= '</tbody></table><br><br><div nobr="true"><table border="1" cellpadding="6" cellspacing="0">'
        . '<tr style="background-color:#0d4d8b;color:#ffffff;"><th colspan="2"><b>PAYMENT REPORT SUMMARY' . ($filtered ? ' (FILTERED ROWS)' : '') . '</b></th></tr>';
    $summary = [
        'Clients / Pending Invoice Headers / Invoices' => $totals['clients'] . ' / ' . $totals['headers'] . ' / ' . $totals['invoices'],
        'Total Invoiced Amount' => 'Rs. ' . aitf_payment_report_amount($totals['total_inv']),
        'Total Amount Received' => 'Rs. ' . aitf_payment_report_amount($totals['total_paid']),
        'Total TDS Deducted' => 'Rs. ' . aitf_payment_report_amount($totals['total_tds']),
        'Net Settlement / Discount Adjustments' => 'Rs. ' . aitf_payment_report_amount($totals['total_disc']),
        'Total Due Pending Amount' => 'Rs. ' . aitf_payment_report_amount($totals['balance']),
    ];
    foreach ($summary as $label => $value) {
        $html .= '<tr><td width="65%"><b>' . $label . '</b></td><td width="35%" align="right"><b>' . $value . '</b></td></tr>';
    }
    $html .= '</table><p style="font-size:8px;color:#64748b;">Pending = Invoiced - Received - TDS - Net Settlement / Discount Adjustments. Summary covers only the invoice headers listed in this report; credits in other headers are not offset.</p></div>';
    return $html;
}

class AITFPaymentReportPDF extends TCPDF {
    public $reportFilters = [];
    public $generatedAt = '';

    public function Header() {
        $logo = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($logo)) $this->Image($logo, 10, 6, 38);
        $this->SetFont('helvetica', 'B', 17);
        $this->SetXY(50, 6);
        $this->Cell(237, 10, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 13);
        $this->SetX(50);
        $this->Cell(237, 8, 'DUE PAYMENTS & FOLLOW-UP REPORT', 0, 1, 'C');
        $filters = $this->reportFilters;
        $details = [];
        if (($filters['clientName'] ?? '') !== '') $details[] = 'Client: ' . $filters['clientName'];
        if (($filters['minAmount'] ?? '') !== '' || ($filters['maxAmount'] ?? '') !== '') {
            $details[] = 'Pending range: ' . (($filters['minAmount'] ?? '') !== '' ? $filters['minAmount'] : '0')
                . ' to ' . (($filters['maxAmount'] ?? '') !== '' ? $filters['maxAmount'] : 'Any');
        }
        $this->SetFont('dejavusans', '', 8);
        $this->SetX(50);
        $this->MultiCell(237, 5, ($details ? implode(' | ', $details) : 'All pending invoice headers') . "\nGenerated: " . $this->generatedAt . ' IST', 0, 'C');
        $this->Line(10, 39, 287, 39);
    }

    public function Footer() {
        $this->SetY(-17);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'System generated report. No physical signature required.', 0, 1, 'C');
        $this->Cell(0, 5, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

function aitf_build_payment_report_pdf(array $payments, array $filters = []) {
    $pdf = new AITFPaymentReportPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->reportFilters = $filters;
    $pdf->generatedAt = (new DateTimeImmutable('now', new DateTimeZone('Asia/Kolkata')))->format('d.m.Y H:i');
    $pdf->SetCreator('AITF');
    $pdf->SetTitle('Due Payments & Follow-Up Report');
    $pdf->SetMargins(10, 43, 10);
    $pdf->SetAutoPageBreak(true, 22);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->writeHTML(aitf_payment_report_html($payments, $filters), true, false, true, false, '');
    return $pdf;
}

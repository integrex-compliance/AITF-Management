<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/payment_due_services_test.php'; // Temporary database fixtures only.
require_once __DIR__ . '/../include/payment_report_pdf.php';
$rows = aitf_get_due_payments($db, ['customerId' => 1]);
$totals = aitf_payment_report_totals($rows);
dueCheck($totals['clients'] === 1 && $totals['headers'] === 2 && $totals['invoices'] === 3, 'Count clients and invoice headers separately');
dueCheck($totals['balance'] == 75.43, 'PDF total is the sum of the same pending rows shown on the page');
dueCheck(abs($totals['total_inv'] - $totals['total_paid'] - $totals['total_tds'] - $totals['total_disc'] - $totals['balance']) < 0.001, 'Summary accounting reconciles');
dueCheck(aitf_payment_report_amount(1234567.8) === '12,34,567.80', 'Indian currency always retains two decimals');
dueCheck(aitf_payment_report_amount(-12.5) === '-12.50', 'Signed excess adjustments display correctly');
$rows[0]['customer_name'] = '<Example & Co>';
$rows[0]['comment'] = "<script>unsafe</script>\nFollow-up";
$html = aitf_payment_report_html($rows);
foreach (['Invoice Header','Invoices Issued','Amount Received','Amount Pending','Comments (If any)','CD/2026/JAN-22','CD/2026/JAN-23','CD/2026/JAN-26','75.43'] as $text) {
    dueCheck(strpos($html,$text) !== false, 'PDF includes required page data: ' . $text);
}
dueCheck(strpos($html,'<script>') === false && strpos($html,'&lt;Example &amp; Co&gt;') !== false, 'Escape customer names and comments in PDF');
$pdf = aitf_build_payment_report_pdf($rows);
$bytes = $pdf->Output('', 'S');
dueCheck(substr($bytes,0,5) === '%PDF-' && strpos(substr($bytes,-30),'%%EOF') !== false, 'Generate a complete PDF without stray output');
$filtered = aitf_get_due_payments($db, ['clientName'=>'example client','minAmount'=>'1','maxAmount'=>'75']);
dueCheck(count($filtered) === 1 && aitf_payment_report_totals($filtered)['balance'] == 75, 'PDF amount and case-insensitive client filters match page semantics');
dueCheck(!aitf_get_due_payments($db,['clientName'=>'%']), 'Percent is a literal client search, not an SQL wildcard');
dueCheck(strpos(aitf_payment_report_html([],['maxAmount'=>'0']),'(filtered)') !== false, 'Zero maximum is an active filter');
dueCheck(aitf_payment_report_totals([])['balance'] == 0, 'Empty reports have zero totals');
$empty = aitf_build_payment_report_pdf([],['maxAmount'=>'0']);
dueCheck(substr($empty->Output('','S'),0,5) === '%PDF-', 'Empty report generates successfully');
$many = aitf_build_payment_report_pdf(array_merge(...array_fill(0,20,$rows)));
dueCheck($many->getNumPages() > 1 && substr($many->Output('','S'),0,5) === '%PDF-', 'Multi-page report generation succeeds');
echo "PASS: PDF page-data parity, totals, filters, escaping, empty and multi-page rendering ($checks cumulative checks).\n";

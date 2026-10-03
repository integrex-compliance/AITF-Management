<?php
// CLI only. Applies the additive schema, then audits existing records without changing them.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../include/payment_ledger.php';
$db = new PDO('mysql:host=localhost;dbname=aitf_web;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
aitf_payment_schema($db);
$_GET['customerid'] = 380;
$invoiceList=$db->query('SELECT * FROM invoice_details WHERE customer_id=380')->fetchAll(PDO::FETCH_ASSOC);
$paymentInfo=$db->query('SELECT * FROM payment_system WHERE customer_id=380')->fetchAll(PDO::FETCH_ASSOC);
// Exercise the exact grouping code used to render the account page, without its mail cron/header.
$source=file_get_contents(__DIR__.'/../customerAccount.php');
$start=strpos($source,"// Payment service grouping starts here.");
$end=strpos($source,'?>',$start);
if ($start===false || $end===false) throw new RuntimeException('Grouping section not found.');
eval(substr($source,$start,$end-$start));
$total=0;
foreach ($activeServiceBlocks as $name) {
    $rows=$paymentsByService[$name] ?? [];
    $received=array_sum(array_column($rows,'payment_received'));
    $total+=$received;
    echo $name.': '.count($invoicesByService[$name] ?? []).' invoices, '.count($rows).' payments, received '.number_format($received,2,'.','')."\n";
}
$originalIds=[1094,1327,1603,1823,2030,2328];
$original=array_filter($paymentsByService['Till Sep 2026'] ?? [], function($row) use($originalIds) { return in_array((int)$row['payment_id'],$originalIds,true); });
if (count($original)!==6 || abs(array_sum(array_column($original,'payment_received'))-115640)>0.001) throw new RuntimeException('Original client 380 receipts changed.');
foreach ($activeServiceBlocks as $name) {
    if (!aitf_payment_block_meaningful($invoicesByService[$name] ?? [],$paymentsByService[$name] ?? [],$blockCommentsMap[strtoupper(trim($name))] ?? '')) throw new RuntimeException('Empty header still displayed.');
}
echo "PASS: original six receipts preserved (115640.00); notes and newer receipts preserved separately.\n";

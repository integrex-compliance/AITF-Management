<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../include/payment_due_helper.php';
require_once __DIR__ . '/../include/payment_settlement.php';

// All fixtures shadow real tables for this connection only. No customer data is changed.
$db = new PDO('mysql:host=localhost;dbname=aitf_web;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = [
    'customer_info' => 'id INT PRIMARY KEY, customer_name VARCHAR(100), unique_id VARCHAR(20), certification_no VARCHAR(20), audit_status VARCHAR(20), contact_person VARCHAR(100), contact VARCHAR(30), email_id VARCHAR(100), is_deleted VARCHAR(5)',
    'payment_page_comments' => 'id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT UNIQUE, comment TEXT, reminder_count INT DEFAULT 0',
    'invoice_details' => 'invoice_id INT PRIMARY KEY, customer_id INT, service_type VARCHAR(100), invoice_type VARCHAR(20), is_deleted VARCHAR(5), final_amount DECIMAL(16,4), invoice_date DATE, final_prefix VARCHAR(100), final_invoice_no VARCHAR(20), invoice_no VARCHAR(100), KEY idx_inv_cust_type (customer_id,invoice_type)',
    'invoice_services' => 'invoice_ids INT, service_desc VARCHAR(200)',
    'payment_system' => 'payment_id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT, payment_header VARCHAR(100), service_type VARCHAR(100), payment_date DATE, payment_received DECIMAL(16,2) DEFAULT 0, tds_amount DECIMAL(16,2) DEFAULT 0, discount_amount DECIMAL(16,2) DEFAULT 0, settlement_kind VARCHAR(10), comment TEXT',
    'payment_block_comments' => 'customer_id INT, service_type VARCHAR(100), header_name VARCHAR(100)',
    'service' => 'customer_id INT, service_type VARCHAR(100), is_deleted VARCHAR(5)',
];
foreach ($tables as $name => $definition) $db->exec("CREATE TEMPORARY TABLE `$name` ($definition)");
$checks = 0;
function dueCheck($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    ++$checks;
}
function dueInvoice($id, $customer, $header, $amount, $type = 'final', $deleted = 'No') {
    global $db;
    $db->prepare('INSERT INTO invoice_details (invoice_id,customer_id,service_type,final_amount,invoice_type,is_deleted,invoice_date,final_prefix,final_invoice_no) VALUES (?,?,?,?,?,?,?, ?,?)')
        ->execute([$id,$customer,$header,$amount,$type,$deleted,'2026-01-31','CD/2026/JAN',(string)$id]);
}
function dueReceipt($customer, $header, $cash, $tds = 0, $discount = 0, $kind = null, $service = 'Payment', $date = '2026-10-03') {
    global $db;
    $db->prepare('INSERT INTO payment_system (customer_id,payment_header,payment_received,tds_amount,discount_amount,settlement_kind,service_type,payment_date) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$customer,$header,$cash,$tds,$discount,$kind,$service,$date]);
}
$db->exec("INSERT INTO customer_info (id,customer_name,is_deleted) VALUES (1,'Example Client','NO'),(2,'Other Client','NO'),(3,'Deleted Client','YES'),(4,'Multi Cycle','NO'),(1191,'Historical Exception','NO')");
$db->exec("INSERT INTO payment_block_comments VALUES (1,'DEC-2025','December invoices'),(1,'JAN-2026','January invoices'),(1,'JAN-2026 (2)','January invoices')");
$db->exec("INSERT INTO payment_page_comments (customer_id,comment) VALUES (1,'Reminder email sent previously')");
dueInvoice(22,1,'DEC-2025',1105001.785);
dueInvoice(23,1,'dec-2025',126001.34);
dueReceipt(1,'DEC-2025',113186.08,12813.59);
dueReceipt(1,'DEC-2025',970171.88,109831.14);
dueReceipt(1,'DEC-2025',22457.28,2542.73);
dueInvoice(24,1,'JAN-2026',66000.94);
dueInvoice(25,1,'JAN-2026',1200003.36);
dueReceipt(1,'JAN-2026',59288.57,6711.64);
dueReceipt(1,'JAN-2026',1077969.32,122034.04);
dueReceipt(1,'JAN-2026',0,0,0.73,'short');
dueInvoice(26,1,'JAN-2026 (2)',100);
dueReceipt(1,'JAN-2026 (2)',25);
dueReceipt(1,'Unallocated',9999999);
dueInvoice(27,1,'Credit',100);
dueReceipt(1,'Credit',10000);
dueInvoice(28,1,'DEC-2025',99999,'proforma');
dueInvoice(29,1,'DEC-2025',99999,'final','Yes');
dueInvoice(30,2,'DEC-2025',200);
dueInvoice(31,3,'DEC-2025',500);
$dues = aitf_get_due_payments($db, ['customerId'=>1]);
$byHeader = array_column($dues, null, 'header_key');
dueCheck(count($dues) === 2, 'Settled, excess, deleted and proforma invoices must not create dues');
dueCheck(isset($byHeader['DEC-2025'], $byHeader['JAN-2026 (2)']), 'Namesake cycles stay independent');
$dec = $byHeader['DEC-2025'];
dueCheck(abs($dec['balance'] - 0.43) < 0.0001, 'Screenshot December remaining must be 0.43');
dueCheck(abs($dec['total_paid'] - 1105815.24) < 0.0001, 'Cash received must exclude TDS');
dueCheck(abs($dec['total_tds'] - 125187.46) < 0.0001, 'TDS is deducted from pending');
dueCheck($dec['invoice_count'] === 2 && array_column($dec['invoices'],'id') === [22,23], 'List every active final invoice once');
dueCheck($dec['invoices'][0]['number'] === 'CD/2026/JAN-22', 'Invoice link number comes from invoice record');
dueCheck($dec['service_type'] === 'December invoices', 'Fetch display name from header catalog');
dueCheck($byHeader['JAN-2026 (2)']['service_type'] === 'January invoices' && $byHeader['JAN-2026 (2)']['balance'] == 75, 'Display labels never merge internal header keys');
dueCheck($dec['reminder_count'] === 1, 'Preserve legacy follow-up reminder counts');
dueCheck(count(aitf_get_due_payments($db,['customerId'=>1,'headerKey'=>'dec-2025'])) === 1, 'Reminder lookup scopes exact customer/header case-insensitively');
dueCheck(!aitf_get_due_payments($db,['customerId'=>1,'headerKey'=>'JAN-2026']), 'Settled header cannot trigger a reminder');
dueCheck(!aitf_get_due_payments($db,['customerId'=>1,'headerKey'=>'January invoices']), 'Display label is not a header identity');
dueCheck(count(aitf_get_due_payments($db,['clientName'=>'Example','minAmount'=>1,'maxAmount'=>100])) === 1, 'Reports filter per-service pending');
dueCheck(count(aitf_get_due_payments($db)) === 3, 'Deleted customers excluded and other customer balances isolated');

dueInvoice(40,2,'CA',100);
dueReceipt(2,null,40,5,10,null,'CA');
dueReceipt(2,null,5,0,0,null,'TDS');
dueReceipt(2,null,3,0,0,null,'short');
dueReceipt(2,'CA',0,0,-2,'excess');
$legacy = aitf_get_due_payments($db,['customerId'=>2,'headerKey'=>'CA'])[0];
dueCheck($legacy['total_paid'] == 40, 'Normal cash plus discount must retain actual cash');
dueCheck($legacy['total_tds'] == 10 && $legacy['total_disc'] == 11 && $legacy['balance'] == 39, 'Legacy TDS, short and signed excess adjustments counted exactly once');
dueInvoice(41,2,'',100);
$db->exec("INSERT INTO invoice_services VALUES (41,'2nd Surveillance')");
dueReceipt(2,null,30,0,0,null,'SA-II');
dueCheck(aitf_get_due_payments($db,['customerId'=>2,'headerKey'=>'SA-II'])[0]['balance'] == 70, 'Invoice service fallback matches Payment System');

$db->exec("INSERT INTO service VALUES (4,'CR','NO'),(4,'RC','NO')");
dueInvoice(50,4,'Payment',500);
dueInvoice(51,4,'RC',200);
dueReceipt(4,null,100,0,0,null,'RC','2024-01-01');
dueReceipt(4,'RC',50,0,0,null,'RC','2024-01-01');
dueReceipt(4,null,20,0,0,null,'RC','2026-10-01');
$multi = array_column(aitf_get_due_payments($db,['customerId'=>4]),null,'header_key');
dueCheck($multi['Till Sep 2026']['balance'] == 400 && $multi['RC']['balance'] == 130, 'Historical cycle fallback respects explicit header and cutoff date');
dueInvoice(52,1191,'',100);
dueReceipt(1191,null,25,0,0,null,'RC','2024-01-01');
dueCheck(aitf_get_due_payments($db,['customerId'=>1191])[0]['balance'] == 75, 'Historical customer exception matches Payment System');
foreach (aitf_get_due_payments($db) as $due) {
    $snapshot = aitf_settlement_snapshot($db,$due['id'],$due['header_key']);
    dueCheck(abs((float)$snapshot['remaining'] - $due['balance']) < 0.0001, 'Due page must match settlement snapshot for every header');
}
echo "PASS: $checks service-wise due payment checks (temporary fixtures only).\n";

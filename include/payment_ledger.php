<?php
require_once __DIR__ . '/payment_service_names.php';
/** Payment grouping and persistence shared by the account page and its endpoints. */
function aitf_payment_schema(PDO $db) {
    static $ready = false;
    if ($ready) return;
    $columns = [
        'payment_header' => 'VARCHAR(100) NULL',
        'client_row_key' => 'VARCHAR(80) NULL',
        'row_version' => 'INT NOT NULL DEFAULT 0',
        'settlement_kind' => 'VARCHAR(10) NULL',
    ];
    foreach ($columns as $name => $definition) {
        if (!$db->query("SHOW COLUMNS FROM payment_system LIKE '$name'")->fetch()) {
            try { $db->exec("ALTER TABLE payment_system ADD COLUMN `$name` $definition"); }
            catch (PDOException $e) { if (($e->errorInfo[1] ?? 0) != 1060) throw $e; }
        }
    }
    if (!$db->query("SHOW INDEX FROM payment_system WHERE Key_name='uniq_customer_payment_key'")->fetch()) {
        try { $db->exec('ALTER TABLE payment_system ADD UNIQUE KEY uniq_customer_payment_key (customer_id,client_row_key)'); }
        catch (PDOException $e) { if (($e->errorInfo[1] ?? 0) != 1061) throw $e; }
    }
    $db->exec("CREATE TABLE IF NOT EXISTS payment_block_comments (
        id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL,
        service_type VARCHAR(100) NOT NULL, comment TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_cust_service (customer_id, service_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    if (!$db->query("SHOW COLUMNS FROM payment_block_comments LIKE 'is_explicit'")->fetch()) {
        try { $db->exec('ALTER TABLE payment_block_comments ADD COLUMN is_explicit TINYINT NOT NULL DEFAULT 0'); }
        catch (PDOException $e) { if (($e->errorInfo[1] ?? 0) != 1060) throw $e; }
    }
    foreach (['header_name'=>'VARCHAR(100) NULL', 'header_order'=>'INT NULL', 'creation_key'=>'VARCHAR(80) NULL'] as $name => $definition) {
        if (!$db->query("SHOW COLUMNS FROM payment_block_comments LIKE '$name'")->fetch()) {
            try { $db->exec("ALTER TABLE payment_block_comments ADD COLUMN `$name` $definition"); }
            catch (PDOException $e) { if (($e->errorInfo[1] ?? 0) != 1060) throw $e; }
        }
    }
    if (!$db->query("SHOW INDEX FROM payment_block_comments WHERE Key_name='uniq_customer_header_creation'")->fetch()) {
        try { $db->exec('ALTER TABLE payment_block_comments ADD UNIQUE KEY uniq_customer_header_creation (customer_id,creation_key)'); }
        catch (PDOException $e) { if (($e->errorInfo[1] ?? 0) != 1061) throw $e; }
    }
    $ready = true;
}

// service_type remains the unique storage key; header_name is the repeatable display label.
function aitf_payment_header_catalog(PDO $db, $customer) {
    $stmt = $db->prepare('SELECT * FROM payment_block_comments WHERE customer_id=? ORDER BY id');
    $stmt->execute([$customer]);
    $catalog = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $catalog[strtoupper(trim($row['service_type']))] = $row;
    return $catalog;
}

function aitf_payment_sort_headers(array $headers, array $catalog) {
    $legacy = ['TILL SEP 2026','CA','SA-I','SA-II','RC','SP','CONTRACT REVIEW','PTR','TRANSITION AUDIT','GENERIC TRAINING','OTHER'];
    $rank = function ($name) use ($catalog, $legacy) {
        $key = strtoupper(trim($name));
        $order = $catalog[$key]['header_order'] ?? null;
        if ($order !== null) return [1, (int)$order];
        $index = array_search($key, $legacy, true);
        return [0, $index === false ? 999 : $index];
    };
    usort($headers, function ($a, $b) use ($rank) { return $rank($a) <=> $rank($b); });
    return $headers;
}

// Called under the customer lock. Assign existing headers an order before appending a cycle.
function aitf_payment_seed_header_order(PDO $db, $customer) {
    $catalog = aitf_payment_header_catalog($db, $customer);
    $names = array_map(function ($r) { return $r['service_type']; }, array_values($catalog));
    foreach (['SELECT DISTINCT service_type FROM invoice_details WHERE customer_id=?',
              "SELECT DISTINCT COALESCE(NULLIF(payment_header,''), NULLIF(service_type,''), 'CA') FROM payment_system WHERE customer_id=?"] as $sql) {
        $stmt = $db->prepare($sql); $stmt->execute([$customer]);
        $names = array_merge($names, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    $names[] = 'Till Sep 2026'; // Reserve the historical bucket without changing legacy records.
    $unique = [];
    foreach ($names as $name) if (trim((string)$name) !== '') $unique[strtoupper(trim($name))] = trim($name);
    $max = 0;
    foreach ($catalog as $row) $max = max($max, (int)($row['header_order'] ?? 0));
    foreach (aitf_payment_sort_headers(array_values($unique), $catalog) as $name) {
        if (isset($catalog[strtoupper($name)]['header_order'])) continue;
        $db->prepare('INSERT INTO payment_block_comments (customer_id,service_type,header_order) VALUES (?,?,?) ON DUPLICATE KEY UPDATE header_order=VALUES(header_order)')->execute([$customer,$name,++$max]);
    }
    return $max;
}

function aitf_payment_allocate_header_key(PDO $db, $customer, $name, $source = '') {
    $catalog = aitf_payment_header_catalog($db, $customer);
    $key = $name; $suffix = 1;
    while (isset($catalog[strtoupper($key)]) && strcasecmp($key,$source) !== 0) {
        $tail = ' (' . (++$suffix) . ')';
        $key = mb_substr($name, 0, 100 - mb_strlen($tail)) . $tail;
    }
    return $key;
}

function aitf_payment_service($value) {
    $value = trim((string)$value);
    if ($value === '' || mb_strlen($value) > 100 || preg_match('/[\x00-\x1f~<>]/u', $value)) {
        throw new InvalidArgumentException('Enter a valid service header (maximum 100 characters).');
    }
    if (preg_match('/consultency|consultancy|consulting|consultant|consult|help|support|internal audit|documentation/i', $value)) {
        throw new InvalidArgumentException('Such type of service is not allowed.');
    }
    return strcasecmp($value, 'Till Sep 2026') === 0 ? 'Till Sep 2026' : $value;
}

function aitf_payment_verified($value) {
    return in_array(strtolower(trim((string)$value)), ['verified', 'yes', '1'], true);
}

function aitf_payment_text_meaningful($value) {
    $value = trim((string)$value);
    return $value !== '' && $value !== '-' && !(is_numeric($value) && (float)$value == 0);
}

function aitf_payment_meaningful(array $row) {
    foreach (['payment_received', 'tds_amount', 'discount_amount', 'amount_decided'] as $field) {
        if (abs((float)($row[$field] ?? 0)) >= 0.005) return true;
    }
    // Any entered detail preserves the row, even before an amount is entered.
    $date = trim((string)($row['payment_date'] ?? ''));
    if (!in_array($date, ['', '-', '0', '0000-00-00', '1970-01-01', '01.01.1970'], true)) return true;
    foreach (['cheque_details', 'comment'] as $field) {
        if (aitf_payment_text_meaningful($row[$field] ?? '')) return true;
    }
    return aitf_payment_verified($row['reconciliation'] ?? '') || aitf_payment_verified($row['tds_verified'] ?? '');
}

function aitf_payment_block_meaningful(array $invoices, array $payments, $comment) {
    // A saved invoice is a real record even if its amount is zero.
    if ($invoices || aitf_payment_text_meaningful($comment)) return true;
    foreach ($payments as $payment) if (aitf_payment_meaningful($payment)) return true;
    return false;
}

function aitf_payment_header(array $row, $multiCycle) {
    $explicit = trim((string)($row['payment_header'] ?? ''));
    if ($explicit !== '') return $explicit;
    $raw = trim((string)($row['service_type'] ?? ''));
    if ($multiCycle) {
        $date = (string)($row['payment_date'] ?? '');
        if ($date > '2026-09-30' && $raw !== '' && strcasecmp($raw, 'Payment') !== 0 && !preg_match('/^CD\//i', $raw)) return $raw;
        return 'Till Sep 2026';
    }
    return function_exists('normalizePaymentServiceType') ? normalizePaymentServiceType($raw) : ($raw ?: 'CA');
}

function aitf_payment_money($value) {
    if ($value === '' || $value === null) return '0.00';
    if (!is_numeric($value) || !is_finite((float)$value) || (float)$value < 0 || (float)$value > 99999999.99) {
        throw new InvalidArgumentException('Payment amounts must be valid non-negative numbers.');
    }
    return number_format((float)$value, 2, '.', '');
}

function aitf_payment_date($value) {
    $value = trim((string)$value);
    if ($value === '') return null;
    foreach (['!d.m.Y', '!Y-m-d'] as $format) {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count'])) && $date->format(substr($format, 1)) === $value) return $date->format('Y-m-d');
    }
    throw new InvalidArgumentException('Enter a valid payment date as DD.MM.YYYY.');
}

function aitf_payment_lock_customer(PDO $db, $customer) {
    if ($customer <= 0) throw new InvalidArgumentException('Invalid customer.');
    // Serialize all ledger mutations for this customer, including first-time saves.
    $stmt = $db->prepare('SELECT id FROM customer_info WHERE id = ? FOR UPDATE');
    $stmt->execute([$customer]);
    if (!$stmt->fetch()) throw new RuntimeException('Customer not found.');
}

function aitf_payment_save(PDO $db, $customer, array $rows) {
    $db->beginTransaction();
    try {
        aitf_payment_lock_customer($db, $customer);
        $stmt = $db->prepare('SELECT payment_sy_id FROM payment_system_id WHERE customer_id = ? ORDER BY payment_sy_id LIMIT 1');
        $stmt->execute([$customer]);
        $payid = (int)$stmt->fetchColumn();
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) throw new InvalidArgumentException('Invalid payment row.');
            $id = (int)($row['id'] ?? 0);
            $key = (string)($row['key'] ?? '');
            if (!$id && $key === '') throw new InvalidArgumentException('New payment is missing its retry key. Reload this page.');
            if ($key !== '' && !preg_match('/^[a-zA-Z0-9_-]{8,80}$/D', $key)) throw new InvalidArgumentException('Invalid payment row key.');
            $old = false;
            if ($id || $key !== '') {
                $stmt = $db->prepare($id ? 'SELECT * FROM payment_system WHERE customer_id = ? AND payment_id = ? FOR UPDATE' : 'SELECT * FROM payment_system WHERE customer_id = ? AND client_row_key = ? FOR UPDATE');
                $stmt->execute([$customer, $id ?: $key]);
                $old = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($id && !$old) throw new RuntimeException('This payment was deleted or belongs to another customer. Reload before saving.');
            }
            if ($old && !$id) {
                // Retried insert after a lost response: return the original ID, never duplicate it.
                $result[] = ['id' => (int)$old['payment_id'], 'key' => $key, 'version' => (int)$old['row_version'], 'replayed' => true];
                continue;
            }
            if ($old && (aitf_payment_verified($old['reconciliation']) || aitf_payment_verified($old['tds_verified']))) {
                throw new RuntimeException('Cannot edit a verified/reconciled payment.');
            }
            if ($old && (!isset($row['version']) || (int)$row['version'] !== (int)$old['row_version'])) {
                throw new RuntimeException('This payment was changed in another window. Reload before saving.');
            }
            $header = aitf_payment_service($row['header'] ?? $row['service_type'] ?? '');
            $service = $old ? $old['service_type'] : $header;
            $received = aitf_payment_money($row['received'] ?? 0);
            $tds = aitf_payment_money($row['tds'] ?? 0);
            if ($old && !empty($old['settlement_kind'])) {
                if (strcasecmp($header, $old['payment_header']) !== 0) throw new RuntimeException('A settlement must stay in its original header.');
                if ((float)$received != 0 || (float)$tds != 0) throw new InvalidArgumentException('Edit the settlement amount, not cash received or TDS.');
                $amount = aitf_payment_money($row['settlement_amount'] ?? abs((float)$old['discount_amount']));
                $discount = number_format(($old['settlement_kind'] === 'excess' ? -1 : 1) * (float)$amount, 2, '.', '');
            } else {
                $discount = aitf_payment_money($row['discount'] ?? 0);
            }
            $date = aitf_payment_date($row['date'] ?? '');
            $instrument = trim((string)($row['instrument'] ?? ''));
            if (mb_strlen($instrument) > 100) throw new InvalidArgumentException('Instrument must be at most 100 characters.');
            if (!$old && !aitf_payment_meaningful(['payment_received'=>$received,'tds_amount'=>$tds,'discount_amount'=>$discount,'payment_date'=>$date,'cheque_details'=>$instrument])) {
                $result[] = ['id' => 0, 'key' => $key, 'version' => 0];
                continue;
            }
            if (!$payid) {
                $stmt = $db->prepare('INSERT INTO payment_system_id (customer_id) VALUES (?)');
                $stmt->execute([$customer]);
                $payid = (int)$db->lastInsertId();
            }
            $total = number_format((float)$received + (float)$tds + (float)$discount, 2, '.', '');
            if ($old) {
                $stmt = $db->prepare('UPDATE payment_system SET payment_header=?, amount_decided=?, payment_received=?, tds_amount=?, discount_amount=?, payment_date=?, cheque_details=?, balance=0, row_version=row_version+1 WHERE payment_id=? AND customer_id=?');
                $stmt->execute([$header, $total, $received, $tds, $discount, $date, $instrument, $id, $customer]);
                $version = (int)$old['row_version'] + 1;
            } else {
                $stmt = $db->prepare("INSERT INTO payment_system (payment_sy_id, customer_id, service_id, service_type, payment_header, amount_decided, payment_received, tds_amount, discount_amount, payment_date, cheque_details, reconciliation, balance, comment, client_row_key) VALUES (?,?,'',?,?,?,?,?,?,?,?, 'Verify',0,'',?)");
                $stmt->execute([$payid, $customer, $service, $header, $total, $received, $tds, $discount, $date, $instrument, $key ?: null]);
                $id = (int)$db->lastInsertId();
                $version = 0;
            }
            $result[] = ['id' => $id, 'key' => $key, 'version' => $version];
        }
        $db->commit();
        return ['success' => true, 'payid' => $payid, 'rows' => $result, 'rowIds' => array_column($result, 'id')];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function aitf_payment_delete(PDO $db, $customer, $id, $version = null) {
    $db->beginTransaction();
    try {
        aitf_payment_lock_customer($db, $customer);
        $stmt = $db->prepare('SELECT * FROM payment_system WHERE payment_id=? AND customer_id=? FOR UPDATE');
        $stmt->execute([$id, $customer]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Payment not found. Reload this page.');
        if (aitf_payment_verified($row['reconciliation']) || aitf_payment_verified($row['tds_verified'])) throw new RuntimeException('Cannot delete a verified/reconciled payment.');
        if ($version !== null && (int)$version !== (int)$row['row_version']) throw new RuntimeException('This payment changed. Reload before deleting.');
        $db->prepare('DELETE FROM payment_system WHERE payment_id=? AND customer_id=?')->execute([$id, $customer]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function aitf_payment_move_invoice(PDO $db, $invoiceId, $target, $customer = 0) {
    $target = aitf_payment_service($target);
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT customer_id FROM invoice_details WHERE invoice_id=?');
        $stmt->execute([$invoiceId]);
        $owner = (int)$stmt->fetchColumn();
        if (!$owner || ($customer && $owner !== (int)$customer)) throw new RuntimeException('Invoice not found for this customer.');
        aitf_payment_lock_customer($db, $owner);
        $db->prepare('UPDATE invoice_details SET service_type=? WHERE invoice_id=? AND customer_id=?')->execute([$target, $invoiceId, $owner]);
        $db->prepare("INSERT INTO payment_block_comments (customer_id,service_type,comment,is_explicit) VALUES (?,?,'',1) ON DUPLICATE KEY UPDATE is_explicit=1")->execute([$owner,$target]);
        $db->commit();
        return $target;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function aitf_payment_verify(PDO $db, $id, $status, $column) {
    if (!in_array($column, ['reconciliation', 'tds_verified'], true) || !in_array($status, ['Verify', 'Verified'], true)) throw new InvalidArgumentException('Invalid verification status.');
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT customer_id FROM payment_system WHERE payment_id=?');
        $stmt->execute([$id]);
        $customer = (int)$stmt->fetchColumn();
        aitf_payment_lock_customer($db,$customer);
        $stmt = $db->prepare('SELECT * FROM payment_system WHERE payment_id=? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new RuntimeException('Payment no longer exists.');
        if ($status === 'Verified' && (float)$row['payment_received'] + (float)$row['tds_amount'] + abs((float)$row['discount_amount']) <= 0) throw new RuntimeException('Enter an amount before verifying a payment.');
        $db->prepare("UPDATE payment_system SET `$column`=?,row_version=row_version+1 WHERE payment_id=?")->execute([$status,$id]);
        $row[$column] = $status;
        $db->commit();
        return ['success'=>true,'status'=>$status,'payment_id'=>$id,'version'=>(int)$row['row_version']+1,'locked'=>aitf_payment_verified($row['tds_verified']) || aitf_payment_verified($row['reconciliation'])];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function aitf_payment_move_receipt(PDO $db, $customer, $id, $target, $version) {
    $target = aitf_payment_service($target);
    $db->beginTransaction();
    try {
        aitf_payment_lock_customer($db, $customer);
        $stmt = $db->prepare('SELECT row_version,settlement_kind FROM payment_system WHERE payment_id=? AND customer_id=? FOR UPDATE');
        $stmt->execute([$id,$customer]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$old) throw new RuntimeException('Payment not found.');
        if (!empty($old['settlement_kind'])) throw new RuntimeException('A settlement belongs to its original header. Delete it and settle the correct header instead.');
        if ($version === null || (int)$version !== (int)$old['row_version']) throw new RuntimeException('This payment changed. Reload before moving it.');
        $db->prepare('UPDATE payment_system SET payment_header=?,row_version=row_version+1 WHERE payment_id=? AND customer_id=?')->execute([$target,$id,$customer]);
        $db->commit();
        return ['success'=>true,'service_type'=>$target,'version'=>(int)$old['row_version']+1];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function aitf_payment_change_header(PDO $db, $customer, $ledgerAction, array $req) {
    try {
        $target = aitf_payment_service($req['service_type'] ?? '');
        $db->beginTransaction();
        aitf_payment_lock_customer($db, $customer);
        $label = $target;
        $maxOrder = aitf_payment_seed_header_order($db, $customer);
        $updatedVersions = [];
        if ($ledgerAction === 'createPaymentHeader') {
            $creationKey = trim((string)($req['creation_key'] ?? ''));
            if ($creationKey !== '' && !preg_match('/^[a-zA-Z0-9_-]{8,80}$/D', $creationKey)) throw new InvalidArgumentException('Invalid header creation key.');
            if ($creationKey !== '') {
                $stmt = $db->prepare('SELECT service_type,header_name FROM payment_block_comments WHERE customer_id=? AND creation_key=?');
                $stmt->execute([$customer,$creationKey]);
                if ($existing = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $db->commit();
                    return ['success'=>true,'service_type'=>$existing['service_type'],'header_name'=>$existing['header_name'],'replayed'=>true];
                }
            }
            $target = aitf_payment_allocate_header_key($db,$customer,$label);
            $db->prepare("INSERT INTO payment_block_comments (customer_id,service_type,header_name,header_order,creation_key,comment,is_explicit) VALUES (?,?,?,?,?,'',1)")->execute([$customer,$target,$label,$maxOrder+1,$creationKey ?: null]);
        } else {
            $source = aitf_payment_service($req['source_service'] ?? $target);
            $paymentIds = json_decode($req['payment_ids'] ?? '[]', true);
            $invoiceIds = json_decode($req['invoice_ids'] ?? '[]', true);
            $versions = json_decode($req['payment_versions'] ?? '{}', true);
            if (!is_array($paymentIds) || !is_array($invoiceIds)) throw new InvalidArgumentException('Invalid header records.');
            if ($ledgerAction === 'removePaymentHeader') {
                $stmt = $db->prepare('SELECT payment_id FROM payment_system WHERE customer_id=? AND payment_header=? LIMIT 1');
                $stmt->execute([$customer,$source]);
                $hasPayments = $stmt->fetch();
                $stmt = $db->prepare('SELECT invoice_id FROM invoice_details WHERE customer_id=? AND service_type=? LIMIT 1');
                $stmt->execute([$customer,$source]);
                if ($hasPayments || $stmt->fetch()) throw new RuntimeException('Move the saved records before removing this header.');
            } else {
                // A rename is one transaction, including invoices, payments and notes.
                $target = aitf_payment_allocate_header_key($db,$customer,$label,$source);
                $stmt = $db->prepare('SELECT comment FROM payment_block_comments WHERE customer_id=? AND service_type=?');
                $stmt->execute([$customer,$source]);
                $comment = $stmt->fetchColumn() ?: '';
                $stmt->execute([$customer,$target]);
                $targetComment = $stmt->fetchColumn();
                if (strcasecmp($source,$target) !== 0 && $targetComment !== false && $targetComment !== '') throw new RuntimeException('That header already has notes. Choose another name.');
                foreach (['payment_system' => $paymentIds, 'invoice_details' => $invoiceIds] as $table => $ids) {
                    $pk = $table === 'payment_system' ? 'payment_id' : 'invoice_id';
                    $column = $table === 'payment_system' ? 'payment_header' : 'service_type';
                    foreach (array_unique(array_map('intval',$ids)) as $id) {
                        $stmt = $db->prepare("SELECT * FROM $table WHERE $pk=? AND customer_id=? FOR UPDATE");
                        $stmt->execute([$id,$customer]);
                        $record = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$record) throw new RuntimeException('A record changed. Reload before renaming this header.');
                        if ($table === 'payment_system' && (!isset($versions[$id]) || (int)$versions[$id] !== (int)$record['row_version'])) throw new RuntimeException('A payment changed. Reload before renaming this header.');
                        $storedHeader = trim((string)($record[$column] ?? ''));
                        if ($storedHeader !== '' && strcasecmp($storedHeader,$source) !== 0) throw new RuntimeException('A record was moved to another header. Reload this page.');
                        $extra = $table === 'payment_system' ? ', row_version=row_version+1' : '';
                        $db->prepare("UPDATE $table SET $column=? $extra WHERE $pk=? AND customer_id=?")->execute([$target,$id,$customer]);
                        if ($table === 'payment_system') $updatedVersions[$id] = (int)$record['row_version'] + 1;
                    }
                }
                // Preserve this header's position and creation key; never overwrite a namesake's notes.
                $stmt = $db->prepare('UPDATE payment_block_comments SET service_type=?,header_name=?,is_explicit=1 WHERE customer_id=? AND service_type=?');
                $stmt->execute([$target,$label,$customer,$source]);
                if (!$stmt->rowCount() && strcasecmp($source,$target) !== 0) throw new RuntimeException('Header changed. Reload before renaming.');
            }
            if ($ledgerAction === 'removePaymentHeader' || strcasecmp($source,$target) !== 0) {
                $db->prepare('DELETE FROM payment_block_comments WHERE customer_id=? AND service_type=?')->execute([$customer,$source]);
            }
        }
        $db->commit();
        return ['success' => true, 'service_type' => $target, 'header_name'=>$label, 'payment_versions'=>$updatedVersions];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

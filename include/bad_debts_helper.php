<?php
require_once __DIR__ . '/payment_ledger.php';
require_once __DIR__ . '/payment_settlement.php';

/**
 * Helper library for managing Bad Debts (Loss Payments)
 */

function aitf_bad_debts_schema(PDO $db) {
    static $schemaEnsured = false;
    if ($schemaEnsured) return;

    try {
        $db->exec("CREATE TABLE IF NOT EXISTS `bad_debts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_id` INT NOT NULL,
            `payment_id` INT NULL,
            `service_type` VARCHAR(100) NOT NULL,
            `invoice_id` INT NULL,
            `invoice_no` VARCHAR(100) NULL,
            `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `note` TEXT NOT NULL,
            `status` ENUM('active', 'reverted') NOT NULL DEFAULT 'active',
            `created_by` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `reverted_by` VARCHAR(100) NULL,
            `reverted_at` DATETIME NULL,
            INDEX `idx_cust` (`customer_id`),
            INDEX `idx_payment` (`payment_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Ensure payment_id is PRIMARY KEY AUTO_INCREMENT in payment_system
        try {
            $stmt = $db->query("SHOW KEYS FROM `payment_system` WHERE Key_name = 'PRIMARY'");
            if (!$stmt || !$stmt->fetch()) {
                $db->exec("ALTER TABLE `payment_system` ADD PRIMARY KEY (`payment_id`)");
            }
            $stmtCol = $db->query("SHOW COLUMNS FROM `payment_system` WHERE Field = 'payment_id'");
            $col = $stmtCol ? $stmtCol->fetch(PDO::FETCH_ASSOC) : null;
            if ($col && strpos(strtolower($col['Extra'] ?? ''), 'auto_increment') === false) {
                $db->exec("ALTER TABLE `payment_system` MODIFY COLUMN `payment_id` INT(11) NOT NULL AUTO_INCREMENT");
            }
        } catch (Throwable $e) {}

        // Ensure settlement_kind in payment_system can hold 'bad_debt'
        try {
            $stmt = $db->query("SHOW COLUMNS FROM `payment_system` LIKE 'settlement_kind'");
            $col = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if ($col && strpos(strtolower($col['Type'] ?? ''), 'varchar(10)') !== false) {
                $db->exec("ALTER TABLE `payment_system` MODIFY COLUMN `settlement_kind` VARCHAR(20) NULL");
            }
        } catch (Throwable $e) {}

        $schemaEnsured = true;
    } catch (Throwable $e) {
        error_log("Bad debts schema error: " . $e->getMessage());
    }
}

/**
 * Access check for Bad Debts: Only Finance, Top Mgmt, and Admin have access
 */
function aitf_can_access_bad_debts($role = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $role = $role === null ? ($_SESSION['role'] ?? '') : $role;
    $cleanRole = strtolower(trim((string)$role));
    $cleanRoleNoSpace = str_replace(array(' ', '-', '_'), '', $cleanRole);

    $isTopMgmt = (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($role))
        || in_array($cleanRole, array('top mgmt', 'director', 'main admin', 'mgmt', 'top management', 'management'), true)
        || in_array($cleanRoleNoSpace, array('topmgmt', 'mainadmin', 'topmanagement'), true);

    $isAdmin = ($cleanRole === 'admin') || (class_exists('Utitlity') && Utitlity::checkForUserAdmin());
    $isFinance = ($cleanRole === 'finance') || (class_exists('Utitlity') && Utitlity::checkForUserFinance());

    $username = strtolower(trim($_SESSION['username'] ?? ''));
    $isSuperUser = in_array($username, array('admin', 'mainadmin', 'director', 'finance'), true);

    return ($isTopMgmt || $isAdmin || $isFinance || $isSuperUser);
}

/**
 * Mark a service remaining due balance as Bad Debt (Loss Payment)
 */
function aitf_mark_bad_debt(PDO $db, $customer, array $req, string $username) {
    if (!aitf_can_access_bad_debts()) {
        throw new RuntimeException('Unauthorized: Only Finance, Admin, or Top Management can manage Bad Debts.');
    }

    $customer = (int)$customer;
    if ($customer <= 0) {
        throw new InvalidArgumentException('Invalid customer ID.');
    }

    aitf_bad_debts_schema($db);

    $header = aitf_payment_service($req['service_type'] ?? '');
    if ($header === '') {
        throw new InvalidArgumentException('Select a valid service header.');
    }

    $amount = (float)($req['bad_debt_amount'] ?? 0);
    $amountCents = (int)round($amount * 100);
    if ($amountCents <= 0) {
        throw new InvalidArgumentException('Enter a bad debt amount greater than zero.');
    }

    $note = trim((string)($req['note'] ?? ''));
    if ($note === '') {
        throw new InvalidArgumentException('Please enter a note / reason for marking as bad debt.');
    }

    $key = trim((string)($req['settlement_key'] ?? ''));
    if ($key === '' || !preg_match('/^[a-zA-Z0-9_-]{8,80}$/D', $key)) {
        $key = 'bd_' . bin2hex(random_bytes(16));
    }

    $db->beginTransaction();
    try {
        aitf_payment_lock_customer($db, $customer);

        // Check if idempotent retry with this key already happened
        $stmtKey = $db->prepare('SELECT id FROM bad_debts WHERE customer_id=? AND payment_id IN (SELECT payment_id FROM payment_system WHERE client_row_key=?)');
        $stmtKey->execute([$customer, $key]);
        if ($existingId = $stmtKey->fetchColumn()) {
            $db->commit();
            return ['success' => true, 'bad_debt_id' => (int)$existingId, 'replayed' => true];
        }

        // Verify remaining balance
        $snapshot = aitf_settlement_snapshot($db, $customer, $header);
        $remainingCents = (int)round((float)$snapshot['remaining'] * 100);
        if ($remainingCents <= 0) {
            throw new RuntimeException('This service has no remaining due balance to mark as bad debt.');
        }

        if ($amountCents > $remainingCents) {
            throw new RuntimeException('Bad debt amount cannot exceed the remaining due balance (₹' . number_format($remainingCents / 100, 2) . ').');
        }

        // Find invoice for this service
        $invoiceId = (int)($req['invoice_id'] ?? 0);
        $invoiceNo = trim((string)($req['invoice_no'] ?? ''));

        if (!$invoiceId && !empty($snapshot['invoices'])) {
            $inv = end($snapshot['invoices']);
            $invoiceId = (int)($inv['id'] ?? 0);
            $invoiceNo = trim((string)($inv['number'] ?? ''));
        }

        if (!$invoiceId) {
            // Try querying final invoice for this customer and service
            $stmtInv = $db->prepare("SELECT invoice_id, final_prefix, final_invoice_no FROM invoice_details WHERE customer_id = ? AND invoice_type = 'final' AND (is_deleted = 'No' OR is_deleted IS NULL) ORDER BY invoice_id DESC LIMIT 1");
            $stmtInv->execute([$customer]);
            $invRow = $stmtInv->fetch(PDO::FETCH_ASSOC);
            if ($invRow) {
                $invoiceId = (int)$invRow['invoice_id'];
                $invoiceNo = trim(($invRow['final_prefix'] ?? '') . '-' . ($invRow['final_invoice_no'] ?? ''));
            }
        }

        // 1. Insert into payment_system as a settlement row
        $stmtSy = $db->prepare('SELECT payment_sy_id FROM payment_system_id WHERE customer_id=? ORDER BY payment_sy_id LIMIT 1');
        $stmtSy->execute([$customer]);
        $payid = (int)$stmtSy->fetchColumn();
        if (!$payid) {
            $db->prepare('INSERT INTO payment_system_id (customer_id) VALUES (?)')->execute([$customer]);
            $payid = (int)$db->lastInsertId();
        }

        $formattedAmt = number_format($amountCents / 100, 2, '.', '');
        $date = date('Y-m-d');
        $paymentComment = $note;
        if ($invoiceNo !== '') {
            $paymentComment = $invoiceNo . ' | ' . $note;
        }

        $stmtPay = $db->prepare("INSERT INTO payment_system (
            payment_sy_id, customer_id, service_id, service_type, payment_header,
            amount_decided, payment_received, tds_amount, discount_amount, payment_date,
            cheque_details, balance, comment, reconciliation, client_row_key, settlement_kind
        ) VALUES (?, ?, '', 'Bad Debt', ?, ?, 0, 0, ?, ?, 'Bad Debt', 0, ?, 'Verify', ?, 'bad_debt')");
        $stmtPay->execute([$payid, $customer, $header, $formattedAmt, $formattedAmt, $date, $paymentComment, $key]);
        $paymentId = (int)$db->lastInsertId();

        // 2. Insert into bad_debts table
        $stmtBd = $db->prepare("INSERT INTO bad_debts (
            customer_id, payment_id, service_type, invoice_id, invoice_no,
            amount, note, status, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())");
        $stmtBd->execute([
            $customer, $paymentId, $header, $invoiceId ?: null, $invoiceNo ?: null,
            $formattedAmt, $note, $username
        ]);
        $badDebtId = (int)$db->lastInsertId();

        $db->commit();
        return [
            'success' => true,
            'bad_debt_id' => $badDebtId,
            'payment_id' => $paymentId,
            'amount' => $formattedAmt,
            'message' => 'Loss payment marked as Bad Debt successfully.'
        ];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Revert a Bad Debt entry back to pending payment
 */
function aitf_revert_bad_debt(PDO $db, int $badDebtId, string $username) {
    if (!aitf_can_access_bad_debts()) {
        throw new RuntimeException('Unauthorized: Only Finance, Admin, or Top Management can revert Bad Debts.');
    }

    aitf_bad_debts_schema($db);

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT * FROM bad_debts WHERE id = ? FOR UPDATE");
        $stmt->execute([$badDebtId]);
        $bd = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bd) {
            throw new RuntimeException('Bad Debt record not found.');
        }

        if ($bd['status'] === 'reverted') {
            $db->commit();
            return ['success' => true, 'already_reverted' => true, 'message' => 'Bad Debt has already been reverted.'];
        }

        $customer = (int)$bd['customer_id'];
        aitf_payment_lock_customer($db, $customer);

        // Remove the settlement row from payment_system so balance reverts back to due
        if (!empty($bd['payment_id'])) {
            $stmtDel = $db->prepare("DELETE FROM payment_system WHERE payment_id = ? AND customer_id = ?");
            $stmtDel->execute([(int)$bd['payment_id'], $customer]);
        }

        // Update bad_debts record status to reverted
        $stmtUpd = $db->prepare("UPDATE bad_debts SET status = 'reverted', reverted_by = ?, reverted_at = NOW() WHERE id = ?");
        $stmtUpd->execute([$username, $badDebtId]);

        $db->commit();
        return [
            'success' => true,
            'bad_debt_id' => $badDebtId,
            'amount' => $bd['amount'],
            'service_type' => $bd['service_type'],
            'message' => 'Bad Debt successfully reverted back to pending payment.'
        ];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Fetch list of Bad Debts with optional filters
 */
function aitf_get_bad_debts(PDO $db, array $filters = []) {
    aitf_bad_debts_schema($db);

    $sql = "SELECT bd.*,
                   ci.customer_name, ci.unique_id, ci.contact_person, ci.contact,
                   inv.final_prefix, inv.final_invoice_no, inv.invoice_date, inv.final_amount as inv_amount, inv.invoice_type
            FROM bad_debts bd
            JOIN customer_info ci ON ci.id = bd.customer_id
            LEFT JOIN invoice_details inv ON inv.invoice_id = bd.invoice_id
            WHERE bd.status = 'active'
              AND ci.is_deleted = 'NO'";

    $params = [];

    if (!empty($filters['clientSearch'])) {
        $searchWords = array_values(array_filter(explode(' ', trim($filters['clientSearch']))));
        if (!empty($searchWords)) {
            $wordConditions = [];
            foreach ($searchWords as $idx => $word) {
                $pName = ':search_' . $idx;
                $wordConditions[] = "(ci.customer_name LIKE $pName OR ci.unique_id LIKE $pName OR ci.id LIKE $pName OR bd.invoice_no LIKE $pName OR bd.note LIKE $pName)";
                $params[$pName] = '%' . $word . '%';
            }
            $sql .= " AND (" . implode(' AND ', $wordConditions) . ")";
        }
    }

    if (!empty($filters['serviceFilter']) && $filters['serviceFilter'] !== 'all') {
        $sql .= " AND bd.service_type = :svcFilter";
        $params[':svcFilter'] = $filters['serviceFilter'];
    }

    if (!empty($filters['startDate'])) {
        $sql .= " AND DATE(bd.created_at) >= :startDate";
        $params[':startDate'] = $filters['startDate'];
    }

    if (!empty($filters['endDate'])) {
        $sql .= " AND DATE(bd.created_at) <= :endDate";
        $params[':endDate'] = $filters['endDate'];
    }

    $sql .= " ORDER BY bd.id DESC";

    try {
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching bad debts: " . $e->getMessage());
        return [];
    }
}

<?php
/**
 * getReminderMailHistory.php
 * 
 * Returns the history of payment reminder emails sent to a client,
 * including date, time, recipient email, CC, service header, and attachments.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

include_once __DIR__ . '/dbConnect.php';

date_default_timezone_set('Asia/Kolkata');

if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
    echo json_encode(['success' => false, 'message' => 'Please sign in with an authorized staff account.']);
    exit;
}

require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("Payment Due")) {
    echo json_encode(['success' => false, 'message' => 'Access Denied. Only authorized roles can view reminder history.']);
    exit;
}

// Ensure reminder logs table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `payment_reminder_mail_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `customer_id` INT NOT NULL,
        `header_key` VARCHAR(150) NULL,
        `service_type` VARCHAR(150) NULL,
        `recipient_email` VARCHAR(255) NOT NULL,
        `cc_emails` VARCHAR(255) NULL,
        `subject` VARCHAR(255) NULL,
        `sent_by` VARCHAR(100) NULL,
        `attachments_count` INT NOT NULL DEFAULT 0,
        `attachments_list` TEXT NULL,
        `sent_at` DATETIME NOT NULL,
        INDEX `idx_prml_cust` (`customer_id`),
        INDEX `idx_prml_sent` (`sent_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

$customerId = isset($_REQUEST['customerId']) ? (int)$_REQUEST['customerId'] : 0;
$headerKey = isset($_REQUEST['headerKey']) ? trim((string)$_REQUEST['headerKey']) : '';

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Customer ID.']);
    exit;
}

// Fetch customer name
$stmtCust = $db->prepare("SELECT customer_name FROM customer_info WHERE id = :id");
$stmtCust->execute([':id' => $customerId]);
$customerName = $stmtCust->fetchColumn() ?: 'Client';

$logs = [];
$seenKeys = [];

// 1. Fetch from structured logs table
try {
    $sql = "SELECT * FROM `payment_reminder_mail_logs` WHERE `customer_id` = :cid ORDER BY `sent_at` DESC, `id` DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':cid' => $customerId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ts = strtotime($row['sent_at']);
        $dtFormatted = date('d M Y, h:i A', $ts);
        $key = date('d.m.Y H:i', $ts) . '|' . strtolower($row['recipient_email']);
        $seenKeys[$key] = true;

        $attList = [];
        if (!empty($row['attachments_list'])) {
            $decoded = json_decode($row['attachments_list'], true);
            if (is_array($decoded)) {
                $attList = $decoded;
            }
        }

        $logs[] = [
            'id' => (int)$row['id'],
            'sent_at' => $row['sent_at'],
            'formatted_datetime' => $dtFormatted,
            'date' => date('d.m.Y', $ts),
            'time' => date('h:i A', $ts),
            'recipient' => $row['recipient_email'],
            'cc' => $row['cc_emails'] ?: 'aitfmcl@gmail.com, aitfacct@gmail.com',
            'subject' => $row['subject'] ?? '',
            'service' => $row['service_type'] ?: ($row['header_key'] ?: 'Certification Services'),
            'header_key' => $row['header_key'] ?? '',
            'sent_by' => $row['sent_by'] ?: 'Staff',
            'attachments_count' => (int)$row['attachments_count'],
            'attachments' => $attList
        ];
    }
} catch (Throwable $e) {}

// 2. Also parse legacy logs from payment_page_comments if any
try {
    $stmtComm = $db->prepare("SELECT comment FROM `payment_page_comments` WHERE `customer_id` = :cid");
    $stmtComm->execute([':cid' => $customerId]);
    $commText = $stmtComm->fetchColumn();
    if (!empty($commText)) {
        $lines = explode("\n", (string)$commText);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^Reminder email sent to\s+(.+?)\s+on\s+([0-9]{2}\.[0-9]{2}\.[0-9]{4}\s+[0-9]{2}:[0-9]{2})(.*)$/i', $line, $m)) {
                $email = trim($m[1]);
                $dtRaw = trim($m[2]);
                $rest = trim($m[3]);

                $key = $dtRaw . '|' . strtolower($email);
                if (isset($seenKeys[$key])) {
                    continue; // Already included from structured table
                }
                $seenKeys[$key] = true;

                $service = '';
                $hKey = '';
                $attCount = 0;
                $attachments = [];

                if (preg_match('/for\s+(.+?)\s*\[([^\]]+)\]/i', $rest, $sm)) {
                    $service = trim($sm[1]);
                    $hKey = trim($sm[2]);
                } elseif (preg_match('/for\s+(.+)/i', $rest, $sm)) {
                    $service = trim($sm[1]);
                }

                if (preg_match('/\[(\d+)\s+attachment[^\:]*:\s*([^\]]+)\]/i', $rest, $am)) {
                    $attCount = (int)$am[1];
                    $attachments = array_map('trim', explode(',', $am[2]));
                }

                $ts = strtotime(str_replace('.', '-', $dtRaw));
                $logs[] = [
                    'id' => 0,
                    'sent_at' => $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s'),
                    'formatted_datetime' => $ts ? date('d M Y, h:i A', $ts) : $dtRaw,
                    'date' => $ts ? date('d.m.Y', $ts) : substr($dtRaw, 0, 10),
                    'time' => $ts ? date('h:i A', $ts) : substr($dtRaw, 11),
                    'recipient' => $email,
                    'cc' => 'aitfmcl@gmail.com, aitfacct@gmail.com',
                    'subject' => '',
                    'service' => $service ?: 'Certification Services',
                    'header_key' => $hKey ?: $service,
                    'sent_by' => 'Staff',
                    'attachments_count' => $attCount,
                    'attachments' => $attachments
                ];
            }
        }
    }
} catch (Throwable $e) {}

// Sort all logs by sent_at descending
usort($logs, function ($a, $b) {
    return strcmp($b['sent_at'], $a['sent_at']);
});

echo json_encode([
    'success' => true,
    'customer_id' => $customerId,
    'customer_name' => $customerName,
    'header_key' => $headerKey,
    'total_count' => count($logs),
    'logs' => $logs
]);

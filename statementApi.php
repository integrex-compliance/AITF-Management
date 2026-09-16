<?php
/**
 * Statement System - AJAX API Backend
 * Handles all CRUD operations for the new statement payment system.
 * 
 * Actions:
 *  - save_payment    : Insert or update a payment row
 *  - delete_payment  : Delete a payment row
 *  - save_reconciliation : Save bank reconciliation + file upload
 *  - get_reconciliation  : Get reconciliation history
 *  - get_payments    : Get all payments for a customer (old + new)
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once './dbConnect.php';
require_once __DIR__ . '/include/statement_helper.php';
date_default_timezone_set('Asia/Kolkata');

if ($db) {
    aitf_ensure_statement_tables($db);
}

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // ───────────────────────────────────────────────
        // SAVE / UPDATE PAYMENT
        // ───────────────────────────────────────────────
        case 'save_payment':
            $id             = $_POST['id'] ?? '';
            $customer_id    = $_POST['customer_id'] ?? '';
            $service_type   = $_POST['service_type'] ?? '';
            $amount_decided = floatval($_POST['amount_decided'] ?? 0);
            $payment_received = floatval($_POST['payment_received'] ?? 0);
            $payment_date   = $_POST['payment_date'] ?? '';
            $instrument     = $_POST['instrument'] ?? '';
            $tds_decided    = floatval($_POST['tds_decided'] ?? 0);
            $tds_received   = floatval($_POST['tds_received'] ?? 0);
            $comment        = $_POST['comment'] ?? '';

            // Calculate balance
            $balance = $amount_decided - $payment_received;

            // Format date
            $date = null;
            if (!empty($payment_date)) {
                // Accept dd.mm.yyyy format
                $parts = explode('.', $payment_date);
                if (count($parts) == 3) {
                    $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                } else {
                    $date = date('Y-m-d', strtotime($payment_date));
                }
            }

            if (!empty($id)) {
                // UPDATE existing
                $sql = "UPDATE statement_payments SET 
                            service_type = :service_type,
                            amount_decided = :amount_decided,
                            payment_received = :payment_received,
                            payment_date = :payment_date,
                            instrument = :instrument,
                            balance = :balance,
                            tds_decided = :tds_decided,
                            tds_received = :tds_received,
                            comment = :comment
                        WHERE id = :id AND customer_id = :customer_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id', $id);
            } else {
                // INSERT new
                $sql = "INSERT INTO statement_payments 
                        (customer_id, service_type, amount_decided, payment_received, payment_date, instrument, balance, tds_decided, tds_received, comment)
                        VALUES 
                        (:customer_id, :service_type, :amount_decided, :payment_received, :payment_date, :instrument, :balance, :tds_decided, :tds_received, :comment)";
                $stmt = $db->prepare($sql);
            }

            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':service_type', $service_type);
            $stmt->bindParam(':amount_decided', $amount_decided);
            $stmt->bindParam(':payment_received', $payment_received);
            $stmt->bindParam(':payment_date', $date);
            $stmt->bindParam(':instrument', $instrument);
            $stmt->bindParam(':balance', $balance);
            $stmt->bindParam(':tds_decided', $tds_decided);
            $stmt->bindParam(':tds_received', $tds_received);
            $stmt->bindParam(':comment', $comment);
            $stmt->execute();

            $newId = !empty($id) ? $id : $db->lastInsertId();
            echo json_encode(['success' => true, 'id' => $newId, 'balance' => $balance]);
            break;

        // ───────────────────────────────────────────────
        // BULK SAVE ALL PAYMENTS (full table save)
        // ───────────────────────────────────────────────
        case 'bulk_save':
            $customer_id = $_POST['customer_id'] ?? '';
            $rows = json_decode($_POST['rows'] ?? '[]', true);

            if (empty($customer_id) || !is_array($rows)) {
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                break;
            }

            $db->beginTransaction();

            foreach ($rows as $row) {
                $id             = $row['id'] ?? '';
                $service_type   = $row['service_type'] ?? '';
                $amount_decided = floatval($row['amount_decided'] ?? 0);
                $payment_received = floatval($row['payment_received'] ?? 0);
                $payment_date   = $row['payment_date'] ?? '';
                $instrument     = $row['instrument'] ?? '';
                $tds_decided    = floatval($row['tds_decided'] ?? 0);
                $tds_received   = floatval($row['tds_received'] ?? 0);
                $comment        = $row['comment'] ?? '';

                $balance = $amount_decided - $payment_received;

                $date = null;
                if (!empty($payment_date)) {
                    $parts = explode('.', $payment_date);
                    if (count($parts) == 3) {
                        $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    } else {
                        $date = date('Y-m-d', strtotime($payment_date));
                    }
                }

                // Skip completely blank new placeholder rows
                if ((empty($id) || strpos($id, 'new_') === 0) && empty(trim($service_type)) && $amount_decided == 0 && $payment_received == 0 && $tds_decided == 0) {
                    continue;
                }

                if (!empty($id) && strpos($id, 'new_') === false) {
                    // UPDATE
                    $sql = "UPDATE statement_payments SET 
                                service_type = :service_type,
                                amount_decided = :amount_decided,
                                payment_received = :payment_received,
                                payment_date = :payment_date,
                                instrument = :instrument,
                                balance = :balance,
                                tds_decided = :tds_decided,
                                tds_received = :tds_received,
                                comment = :comment
                            WHERE id = :id AND customer_id = :customer_id";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':id', $id);
                } else {
                    // INSERT
                    $sql = "INSERT INTO statement_payments 
                            (customer_id, service_type, amount_decided, payment_received, payment_date, instrument, balance, tds_decided, tds_received, comment)
                            VALUES 
                            (:customer_id, :service_type, :amount_decided, :payment_received, :payment_date, :instrument, :balance, :tds_decided, :tds_received, :comment)";
                    $stmt = $db->prepare($sql);
                }

                $stmt->bindParam(':customer_id', $customer_id);
                $stmt->bindParam(':service_type', $service_type);
                $stmt->bindParam(':amount_decided', $amount_decided);
                $stmt->bindParam(':payment_received', $payment_received);
                $stmt->bindParam(':payment_date', $date);
                $stmt->bindParam(':instrument', $instrument);
                $stmt->bindParam(':balance', $balance);
                $stmt->bindParam(':tds_decided', $tds_decided);
                $stmt->bindParam(':tds_received', $tds_received);
                $stmt->bindParam(':comment', $comment);
                $stmt->execute();
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'All payments saved successfully']);
            break;

        // ───────────────────────────────────────────────
        // DELETE PAYMENT
        // ───────────────────────────────────────────────
        case 'delete_payment':
            $id = $_REQUEST['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'No ID provided']);
                break;
            }

            $stmt = $db->prepare("DELETE FROM statement_payments WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            echo json_encode(['success' => true]);
            break;

        // ───────────────────────────────────────────────
        // SAVE BANK RECONCILIATION
        // ───────────────────────────────────────────────
        case 'save_reconciliation':
            $customer_id = $_POST['customer_id'] ?? '';
            $acknowledged_by = $_SESSION['username'] ?? $_SESSION['name'] ?? 'Unknown';
            $acknowledgement_text = $_POST['acknowledgement_text'] ?? '';

            $bank_file = null;
            $original_filename = null;

            // Handle file upload
            if (isset($_FILES['bank_statement']) && $_FILES['bank_statement']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/bank_statements/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $original_filename = $_FILES['bank_statement']['name'];
                $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
                $newFilename = 'bank_' . $customer_id . '_' . date('Ymd_His') . '.' . $ext;
                $targetPath = $uploadDir . $newFilename;

                if (move_uploaded_file($_FILES['bank_statement']['tmp_name'], $targetPath)) {
                    $bank_file = 'uploads/bank_statements/' . $newFilename;
                }
            }

            $sql = "INSERT INTO statement_bank_reconciliation 
                    (customer_id, acknowledged_by, acknowledged_at, acknowledgement_text, bank_statement_file, original_filename)
                    VALUES 
                    (:customer_id, :acknowledged_by, NOW(), :acknowledgement_text, :bank_file, :original_filename)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':acknowledged_by', $acknowledged_by);
            $stmt->bindParam(':acknowledgement_text', $acknowledgement_text);
            $stmt->bindParam(':bank_file', $bank_file);
            $stmt->bindParam(':original_filename', $original_filename);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Bank reconciliation saved']);
            break;

        // ───────────────────────────────────────────────
        // GET RECONCILIATION HISTORY
        // ───────────────────────────────────────────────
        case 'get_reconciliation':
            $customer_id = $_GET['customer_id'] ?? '';

            $stmt = $db->prepare("SELECT * FROM statement_bank_reconciliation WHERE customer_id = :cid ORDER BY acknowledged_at DESC");
            $stmt->bindParam(':cid', $customer_id);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $records]);
            break;

        // ───────────────────────────────────────────────
        // GET ALL PAYMENTS (old + new) for a customer
        // ───────────────────────────────────────────────
        case 'get_payments':
            $customer_id = $_GET['customer_id'] ?? '';

            // New statement payments
            $stmt = $db->prepare("SELECT * FROM statement_payments WHERE customer_id = :cid ORDER BY id ASC");
            $stmt->bindParam(':cid', $customer_id);
            $stmt->execute();
            $newPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Old payment system records
            $stmt2 = $db->prepare("SELECT ps.* FROM payment_system ps 
                                   INNER JOIN payment_system_id psi ON ps.payment_sy_id = psi.payment_sy_id
                                   WHERE ps.customer_id = :cid AND ps.service_type NOT LIKE '%tds%'
                                   ORDER BY ps.payment_id ASC");
            $stmt2->bindParam(':cid', $customer_id);
            $stmt2->execute();
            $oldPayments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'new' => $newPayments, 'old' => $oldPayments]);
            break;

        // ───────────────────────────────────────────────
        // VIEW BANK STATEMENT FILE
        // ───────────────────────────────────────────────
        case 'view_bank_file':
            $fileId = $_GET['file_id'] ?? '';
            $stmt = $db->prepare("SELECT bank_statement_file, original_filename FROM statement_bank_reconciliation WHERE id = :id");
            $stmt->bindParam(':id', $fileId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['bank_statement_file'])) {
                $filePath = __DIR__ . '/' . $row['bank_statement_file'];
                if (file_exists($filePath)) {
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'pdf' => 'application/pdf',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'xls' => 'application/vnd.ms-excel',
                    ];
                    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

                    header('Content-Type: ' . $mime);
                    header('Content-Disposition: inline; filename="' . $row['original_filename'] . '"');
                    header('Content-Length: ' . filesize($filePath));
                    readfile($filePath);
                    exit;
                }
            }

            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'File not found']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

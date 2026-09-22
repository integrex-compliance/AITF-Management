<?php
ob_start();
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

function sendJsonResponse($data) {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'getClientInfo') {
    $customerId = intval($_REQUEST['customer_id'] ?? 0);
    try {
        if ($customerId <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Invalid customer ID']);
        }

        // Fetch customer info
        $stmt = $db->prepare("SELECT id, customer_name, unique_id FROM customer_info WHERE id = :id");
        $stmt->bindParam(':id', $customerId);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            sendJsonResponse(['success' => false, 'message' => 'Customer not found']);
        }

        // Fetch final invoices
        $stmtInv = $db->prepare("SELECT invoice_id, final_prefix, final_invoice_no, invoice_date, net_price, final_amount, invoice_status FROM invoice_details WHERE customer_id = :id AND invoice_type = 'final' AND (is_deleted = 'No' OR is_deleted IS NULL) ORDER BY invoice_id ASC");
        $stmtInv->bindParam(':id', $customerId);
        $stmtInv->execute();
        $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        $totalInvoice = 0;
        $totalNetPrice = 0;
        foreach ($invoices as $inv) {
            $totalInvoice += floatval($inv['final_amount'] ?? 0);
            $totalNetPrice += floatval($inv['net_price'] ?? 0);
        }

        // Fetch existing payments
        $stmtPay = $db->prepare("SELECT * FROM payment_system WHERE customer_id = :id ORDER BY payment_id ASC");
        $stmtPay->bindParam(':id', $customerId);
        $stmtPay->execute();
        $payments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

        $existingTds = null;
        $totalReceived = 0;
        foreach ($payments as $p) {
            $totalReceived += floatval($p['payment_received'] ?? 0);
            if (stripos($p['service_type'], 'tds') !== false) {
                $existingTds = $p;
            }
        }

        sendJsonResponse([
            'success' => true,
            'customer' => $customer,
            'invoices' => $invoices,
            'total_invoice' => $totalInvoice,
            'total_net_price' => $totalNetPrice,
            'payments' => $payments,
            'total_received' => $totalReceived,
            'existing_tds' => $existingTds
        ]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

if ($action === 'saveTds') {
    try {
        $customerId = intval($_REQUEST['customer_id'] ?? 0);
        $tdsAmount = floatval($_REQUEST['tds_amount'] ?? 0);
        $tdsReceived = floatval($_REQUEST['tds_received'] ?? 0);
        $tdsDateStr = trim($_REQUEST['tds_date'] ?? '');
        $tdsCert = trim($_REQUEST['tds_cert'] ?? '');
        $adjustService = isset($_REQUEST['adjust_service']) && ($_REQUEST['adjust_service'] === '1' || $_REQUEST['adjust_service'] === 'true' || $_REQUEST['adjust_service'] === 1);

        if ($customerId <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Please select a valid customer']);
        }

        if ($tdsAmount <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'TDS amount must be greater than 0']);
        }

        if ($tdsReceived > $tdsAmount) {
            sendJsonResponse(['success' => false, 'message' => 'TDS received cannot be greater than TDS decided amount']);
        }

        $date = null;
        if (!empty($tdsDateStr)) {
            $parts = explode('.', $tdsDateStr);
            if (count($parts) === 3) {
                $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } else {
                $date = date('Y-m-d', strtotime($tdsDateStr));
            }
        }

        $tdsBalance = $tdsAmount - $tdsReceived;

        // Check or create payment_system_id
        $stmt = $db->prepare("SELECT payment_sy_id FROM payment_system_id WHERE customer_id = :cid ORDER BY payment_sy_id DESC LIMIT 1");
        $stmt->bindParam(':cid', $customerId);
        $stmt->execute();
        $payIdRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payIdRow) {
            $stmt = $db->prepare("INSERT INTO payment_system_id (customer_id) VALUES (:cid)");
            $stmt->bindParam(':cid', $customerId);
            $stmt->execute();
            $paySyId = $db->lastInsertId();
        } else {
            $paySyId = $payIdRow['payment_sy_id'];
        }

        // Check if TDS row already exists in payment_system
        $stmt = $db->prepare("SELECT payment_id, amount_decided, payment_received FROM payment_system WHERE customer_id = :cid AND service_type LIKE '%tds%' ORDER BY payment_id DESC LIMIT 1");
        $stmt->bindParam(':cid', $customerId);
        $stmt->execute();
        $existingTdsRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingTdsRow) {
            // UPDATE existing TDS row
            $stmtUpdate = $db->prepare("UPDATE payment_system SET 
                service_type = 'TDS', 
                amount_decided = :amount, 
                payment_received = :rcvd, 
                payment_date = :pdate, 
                cheque_details = :cert, 
                balance = :bal, 
                comment = :comment 
                WHERE payment_id = :pid");
            $stmtUpdate->bindParam(':amount', $tdsAmount);
            $stmtUpdate->bindParam(':rcvd', $tdsReceived);
            $stmtUpdate->bindParam(':pdate', $date);
            $stmtUpdate->bindParam(':cert', $tdsCert);
            $stmtUpdate->bindParam(':bal', $tdsBalance);
            $stmtUpdate->bindParam(':comment', $tdsCert);
            $stmtUpdate->bindParam(':pid', $existingTdsRow['payment_id']);
            $stmtUpdate->execute();
            $tdsPaymentId = $existingTdsRow['payment_id'];
        } else {
            // INSERT new TDS row
            $stmtInsert = $db->prepare("INSERT INTO payment_system 
                (payment_sy_id, customer_id, service_type, amount_decided, payment_received, payment_date, cheque_details, balance, comment) 
                VALUES (:syid, :cid, 'TDS', :amount, :rcvd, :pdate, :cert, :bal, :comment)");
            $stmtInsert->bindParam(':syid', $paySyId);
            $stmtInsert->bindParam(':cid', $customerId);
            $stmtInsert->bindParam(':amount', $tdsAmount);
            $stmtInsert->bindParam(':rcvd', $tdsReceived);
            $stmtInsert->bindParam(':pdate', $date);
            $stmtInsert->bindParam(':cert', $tdsCert);
            $stmtInsert->bindParam(':bal', $tdsBalance);
            $stmtInsert->bindParam(':comment', $tdsCert);
            $stmtInsert->execute();
            $tdsPaymentId = $db->lastInsertId();
        }

        // Bug Fix: Adjust main service row so total decided does NOT inflate to 1200 when invoice is 1000!
        if ($adjustService) {
            // Get invoice total
            $stmtInv = $db->prepare("SELECT SUM(final_amount) as total_inv FROM invoice_details WHERE customer_id = :cid AND invoice_type = 'final' AND (is_deleted = 'No' OR is_deleted IS NULL)");
            $stmtInv->bindParam(':cid', $customerId);
            $stmtInv->execute();
            $invRow = $stmtInv->fetch(PDO::FETCH_ASSOC);
            $totalInvoice = floatval($invRow['total_inv'] ?? 0);

            if ($totalInvoice > 0) {
                // Find non-TDS payment rows
                $stmtMain = $db->prepare("SELECT payment_id, service_type, amount_decided, payment_received FROM payment_system WHERE customer_id = :cid AND service_type NOT LIKE '%tds%' ORDER BY payment_id ASC LIMIT 1");
                $stmtMain->bindParam(':cid', $customerId);
                $stmtMain->execute();
                $mainRow = $stmtMain->fetch(PDO::FETCH_ASSOC);

                if ($mainRow) {
                    $mainDecided = floatval($mainRow['amount_decided']);
                    $mainReceived = floatval($mainRow['payment_received']);
                    // If main row + TDS exceeds totalInvoice OR main row is equal to totalInvoice
                    if ($mainDecided >= $totalInvoice || ($mainDecided + $tdsAmount) > $totalInvoice) {
                        $newMainDecided = max(0, $totalInvoice - $tdsAmount);
                        $newMainBalance = max(0, $newMainDecided - $mainReceived);
                        $stmtAdj = $db->prepare("UPDATE payment_system SET amount_decided = :newdec, balance = :newbal WHERE payment_id = :pid");
                        $stmtAdj->bindParam(':newdec', $newMainDecided);
                        $stmtAdj->bindParam(':newbal', $newMainBalance);
                        $stmtAdj->bindParam(':pid', $mainRow['payment_id']);
                        $stmtAdj->execute();
                    }
                }
            }
        }

        sendJsonResponse([
            'success' => true, 
            'message' => 'TDS details saved successfully!',
            'payment_id' => $tdsPaymentId
        ]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

if ($action === 'removeTds') {
    try {
        $customerId = intval($_REQUEST['customer_id'] ?? 0);
        if ($customerId <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'Invalid customer ID']);
        }

        // Find TDS row
        $stmt = $db->prepare("SELECT payment_id, amount_decided FROM payment_system WHERE customer_id = :cid AND service_type LIKE '%tds%'");
        $stmt->bindParam(':cid', $customerId);
        $stmt->execute();
        $tdsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $removedAmount = 0;
        foreach ($tdsRows as $tr) {
            $removedAmount += floatval($tr['amount_decided']);
            $delStmt = $db->prepare("DELETE FROM payment_system WHERE payment_id = :pid");
            $delStmt->bindParam(':pid', $tr['payment_id']);
            $delStmt->execute();
        }

        // Restore main service decided amount if invoice exists
        $stmtInv = $db->prepare("SELECT SUM(final_amount) as total_inv FROM invoice_details WHERE customer_id = :cid AND invoice_type = 'final' AND (is_deleted = 'No' OR is_deleted IS NULL)");
        $stmtInv->bindParam(':cid', $customerId);
        $stmtInv->execute();
        $invRow = $stmtInv->fetch(PDO::FETCH_ASSOC);
        $totalInvoice = floatval($invRow['total_inv'] ?? 0);

        if ($totalInvoice > 0) {
            $stmtMain = $db->prepare("SELECT payment_id, service_type, amount_decided, payment_received FROM payment_system WHERE customer_id = :cid AND service_type NOT LIKE '%tds%' ORDER BY payment_id ASC LIMIT 1");
            $stmtMain->bindParam(':cid', $customerId);
            $stmtMain->execute();
            $mainRow = $stmtMain->fetch(PDO::FETCH_ASSOC);

            if ($mainRow) {
                $mainReceived = floatval($mainRow['payment_received']);
                $newMainBalance = max(0, $totalInvoice - $mainReceived);
                $stmtAdj = $db->prepare("UPDATE payment_system SET amount_decided = :newdec, balance = :newbal WHERE payment_id = :pid");
                $stmtAdj->bindParam(':newdec', $totalInvoice);
                $stmtAdj->bindParam(':newbal', $newMainBalance);
                $stmtAdj->bindParam(':pid', $mainRow['payment_id']);
                $stmtAdj->execute();
            }
        }

        sendJsonResponse(['success' => true, 'message' => 'TDS removed successfully']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

sendJsonResponse(['success' => false, 'message' => 'Invalid action']);

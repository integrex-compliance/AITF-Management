<?php

session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
$req = array_merge($_GET, $_POST);
if (empty($_SESSION['username']) || in_array(strtolower($_SESSION['role'] ?? ''), ['client', 'external'], true)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Please sign in with a staff account.']);
    exit;
}
require_once __DIR__ . '/include/payment_ledger_actions.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (isset($_GET["invid"]) && !empty($_GET["invid"])) {
    try {
        $invrcv = "UPDATE `invoice_details` SET `invoice_status`= 'Rcv' WHERE `invoice_id` = :invid";
        $statement = $db->prepare($invrcv);
        $statement->bindParam(':invid', $_GET["invid"]);
        $statement->execute();
        echo 'success';
        exit;
    } catch (Exception $ex) {
        echo $ex;
    }
}

if(isset($_GET["inv_id"]) && !empty($_GET["inv_id"])){
    try {
        $invrcv = "UPDATE `invoice_details` SET `invoice_status`= 'NotRcv' WHERE `invoice_id` = :invid";
        $statement = $db->prepare($invrcv);
        $statement->bindParam(':invid', $_GET["inv_id"]);
        $statement->execute();
        echo 'success';
        exit;
    } catch (Exception $ex) {
        echo $ex;
    }
}

if (isset($req['action']) && $req['action'] === 'saveServiceBlockComment') {
    header('Content-Type: application/json');
    try {
        $customerId = intval($req['customer_id'] ?? 0);
        $serviceType = trim((string)($req['service_type'] ?? ''));
        $comment = trim((string)($req['comment'] ?? ''));
        if ($customerId > 0 && $serviceType !== '') {
            $stmt = $db->prepare("INSERT INTO `payment_block_comments` (`customer_id`, `service_type`, `comment`) 
                                  VALUES (:cid, :svc, :comm) 
                                  ON DUPLICATE KEY UPDATE `comment` = :comm2");
            $stmt->bindParam(':cid', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':svc', $serviceType);
            $stmt->bindParam(':comm', $comment);
            $stmt->bindParam(':comm2', $comment);
            $stmt->execute();
            echo json_encode(['success' => true, 'comment' => $comment, 'service_type' => $serviceType]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (isset($req['action']) && $req['action'] === 'updateInvoiceRemark') {
    header('Content-Type: application/json');
    try {
        $invId = intval($req['invoice_id'] ?? 0);
        $remark = trim((string)($req['remark'] ?? ''));
        if ($invId > 0) {
            $stmt = $db->prepare("UPDATE `invoice_details` SET `remark` = :rmk WHERE `invoice_id` = :id");
            $stmt->bindParam(':rmk', $remark);
            $stmt->bindParam(':id', $invId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'remark' => $remark]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (!function_exists('aitf_is_blocked_service_name')) {
    function aitf_is_blocked_service_name($name) {
        if (empty($name)) return false;
        $lower = strtolower(trim((string)$name));
        $blocked = ['consultency', 'consultancy', 'consulting', 'consultant', 'consult', 'help', 'support', 'internal audit', 'documentation'];
        foreach ($blocked as $b) {
            if (strpos($lower, $b) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (isset($req['action']) && $req['action'] === 'updatePaymentComment') {
    header('Content-Type: application/json');
    try {
        $payId = intval($req['payment_id'] ?? 0);
        $comment = trim((string)($req['comment'] ?? ''));
        $isStatement = !empty($req['is_statement']) && $req['is_statement'] !== '0' && $req['is_statement'] !== false;
        if ($payId > 0) {
            if ($isStatement) {
                $stmt = $db->prepare("UPDATE `statement_payments` SET `comment` = :comm WHERE `id` = :id");
            } else {
                $stmt = $db->prepare("UPDATE `payment_system` SET `comment` = :comm WHERE `payment_id` = :id");
            }
            $stmt->bindParam(':comm', $comment);
            $stmt->bindParam(':id', $payId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'comment' => $comment, 'payment_id' => $payId]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (isset($req['action']) && $req['action'] === 'updateReconcileStatus') {
    header('Content-Type: application/json');
    try {
        $userRole = trim($_SESSION['role'] ?? '');
        $canReconcile = (
            (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance()))
            || in_array(trim($userRole), array('Top Mgmt', 'Admin', 'Finance', 'Director', 'Management', 'Main Admin'), true)
            || (isset($_SESSION['username']) && in_array(strtolower(trim($_SESSION['username'])), array('admin', 'mainadmin', 'director', 'finance'), true))
        );

        if (!$canReconcile) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Finance, Admin, and Management can reconcile payments.']);
            exit;
        }

        $payId = intval($req['payment_id'] ?? 0);
        $status = trim((string)($req['status'] ?? 'Verify'));
        $isStatement = !empty($req['is_statement']) && $req['is_statement'] !== '0' && $req['is_statement'] !== false;
        if ($payId > 0) {
            if ($isStatement) {
                $stmt = $db->prepare("UPDATE `statement_payments` SET `tds_verified` = :status WHERE `id` = :id");
            } else {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for verification.');
                aitf_payment_schema($db);
                aitf_payment_json(aitf_payment_verify($db, $payId, $status, 'reconciliation'));
            }
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $payId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'status' => $status, 'payment_id' => $payId]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (isset($req['action']) && $req['action'] === 'updateTdsVerifyStatus') {
    header('Content-Type: application/json');
    try {
        $tdsRole = trim($_SESSION['role'] ?? '');
        $canVerifyTds = (
            (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserAdmin() || Utitlity::checkForUserFinance()))
            || in_array(trim($tdsRole), array('Top Mgmt', 'Admin', 'Finance', 'Director', 'Management'), true)
            || (isset($_SESSION['username']) && in_array(strtolower(trim($_SESSION['username'])), array('admin', 'mainadmin', 'director', 'finance'), true))
        );

        if (!$canVerifyTds) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Top Mgmt, Admin, and Finance can verify TDS.']);
            exit;
        }

        $payId = intval($req['payment_id'] ?? 0);
        $status = trim((string)($req['status'] ?? 'Verify'));
        $isStatement = !empty($req['is_statement']) && $req['is_statement'] !== '0' && $req['is_statement'] !== false;
        if ($payId > 0) {
            if ($isStatement) {
                $stmt = $db->prepare("UPDATE `statement_payments` SET `tds_verified` = :status WHERE `id` = :id");
            } else {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for verification.');
                aitf_payment_schema($db);
                aitf_payment_json(aitf_payment_verify($db, $payId, $status, 'tds_verified'));
            }
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $payId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'status' => $status, 'payment_id' => $payId]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if (isset($req['action']) && $req['action'] === 'saveDiscount') {
    header('Content-Type: application/json');
    try {
        $customerId = $req['customer_id'] ?? '';
        $discountAmount = floatval($req['discount_amount'] ?? 0);
        $discountDateStr = $req['discount_date'] ?? '';
        $invoiceNo = trim($req['invoice_no'] ?? '');
        $invoiceId = trim($req['invoice_id'] ?? '');
        $remarks = trim($req['remarks'] ?? 'Short Paid');

        if (empty($customerId) || $discountAmount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid customer or discount amount.']);
            exit;
        }

        $date = !empty($discountDateStr) ? date("Y-m-d", strtotime($discountDateStr)) : date("Y-m-d");

        $findPayId = "SELECT payment_sy_id FROM payment_system WHERE customer_id = :id ORDER BY payment_id DESC LIMIT 1";
        $stmt = $db->prepare($findPayId);
        $stmt->bindParam(':id', $customerId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['payment_sy_id'])) {
            $pay_id = $row['payment_sy_id'];
        } else {
            $insertPayId = "INSERT INTO payment_system_id (customer_id) VALUES (:customer)";
            $stmt = $db->prepare($insertPayId);
            $stmt->bindParam(':customer', $customerId);
            $stmt->execute();
            $pay_id = $db->lastInsertId();
        }

        $serviceType = !empty($req['service_type']) ? trim($req['service_type']) : "Discount";
        $zero = 0.00;
        
        $chequeDetails = !empty($invoiceNo) ? $invoiceNo : $remarks;
        $comment = !empty($invoiceNo) ? ("Discount against Invoice " . $invoiceNo . ($remarks && $remarks !== $invoiceNo ? " (" . $remarks . ")" : "")) : $remarks;

        aitf_payment_schema($db);
        $insertDiscount = "INSERT INTO payment_system (payment_sy_id, customer_id, service_type, payment_header, amount_decided, payment_received, tds_amount, discount_amount, payment_date, cheque_details, balance, comment) "
            . "VALUES (:payid, :customer, :services, :paymentHeader, :decideAmount, :rcvAmount, :tdsAmount, :discountAmount, :payDate, :checkdetails, :balance, :comment)";
        $stmt = $db->prepare($insertDiscount);
        $stmt->bindParam(':payid', $pay_id);
        $stmt->bindParam(':customer', $customerId);
        $stmt->bindParam(':services', $serviceType);
        $stmt->bindParam(':paymentHeader', $serviceType);
        $stmt->bindParam(':decideAmount', $discountAmount);
        $stmt->bindParam(':rcvAmount', $zero);
        $stmt->bindParam(':tdsAmount', $zero);
        $stmt->bindParam(':discountAmount', $discountAmount);
        $stmt->bindParam(':payDate', $date);
        $stmt->bindParam(':checkdetails', $chequeDetails);
        $stmt->bindParam(':balance', $zero);
        $stmt->bindParam(':comment', $comment);
        $stmt->execute();

        if (empty($invoiceId) && !empty($invoiceNo)) {
            $parts = explode('-', $invoiceNo, 2);
            if (count($parts) === 2) {
                $findInv = "SELECT invoice_id FROM invoice_details WHERE customer_id = :cid AND final_prefix = :pfx AND final_invoice_no = :no LIMIT 1";
                $st = $db->prepare($findInv);
                $st->bindParam(':cid', $customerId);
                $st->bindParam(':pfx', $parts[0]);
                $st->bindParam(':no', $parts[1]);
                $st->execute();
                $invRow = $st->fetch(PDO::FETCH_ASSOC);
                if ($invRow && !empty($invRow['invoice_id'])) {
                    $invoiceId = $invRow['invoice_id'];
                }
            }
        }

        if (!empty($invoiceId)) {
            $updInv = "UPDATE `invoice_details` SET `invoice_status`= 'Rcv' WHERE `invoice_id` = :invid";
            $stmt = $db->prepare($updInv);
            $stmt->bindParam(':invid', $invoiceId);
            $stmt->execute();
        }

        echo json_encode(['success' => true, 'message' => 'Discount settlement saved successfully.']);
        exit;
    } catch (Exception $ex) {
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        exit;
    }
}

aitf_payment_json(['success' => false, 'message' => 'Unsupported payment request. Reload the account page.']);

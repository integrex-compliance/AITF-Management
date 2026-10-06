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
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for verification.');
                aitf_payment_json(aitf_payment_verify($db, $payId, $status, 'reconciliation', true));
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
            if ($status === 'Verified') {
                // If tds_received was submitted with verify request, persist it first
                if (isset($req['tds_received']) && $req['tds_received'] !== '') {
                    $cleanedRec = number_format(max(0.0, floatval(str_replace(',', '', $req['tds_received']))), 2, '.', '');
                    if ($isStatement) {
                        $upStmt = $db->prepare("UPDATE statement_payments SET tds_received = ? WHERE id = ?");
                        $upStmt->execute([$cleanedRec, $payId]);
                    } else {
                        $upStmt = $db->prepare("UPDATE payment_system SET tds_received = ? WHERE payment_id = ?");
                        $upStmt->execute([$cleanedRec, $payId]);
                    }
                }
            }

            if ($isStatement) {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for verification.');
                aitf_payment_json(aitf_payment_verify($db, $payId, $status, 'tds_verified', true));
            } else {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Use POST for verification.');
                aitf_payment_schema($db);
                aitf_payment_json(aitf_payment_verify($db, $payId, $status, 'tds_verified'));
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid payment ID']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

aitf_payment_json(['success' => false, 'message' => 'Unsupported payment request. Reload the account page.']);

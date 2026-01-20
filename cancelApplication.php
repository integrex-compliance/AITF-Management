<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
    header('Location: customerAccount.php');
    exit;
}

$customerId = (int)$_POST['customer_id'];
$reason     = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : '';

try {
    // Fetch existing comment and current status (if any)
    $sql = "SELECT comment, audit_status FROM customer_info WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $newComment  = $row && !empty($row['comment']) ? $row['comment'] : '';
    $prevStatus  = $row && !empty($row['audit_status']) ? $row['audit_status'] : '';

    $metaLines = array();

    // Store previous status for future re-enable, but avoid nesting Application canceled
    if ($prevStatus !== '' && $prevStatus !== 'Application canceled') {
        $metaLines[] = 'Previous audit_status: ' . $prevStatus;
    }

    if ($reason !== '') {
        $metaLines[] = 'Application canceled reason: ' . $reason;
    }

    if (!empty($metaLines)) {
        $metaBlock = implode("\n", $metaLines);
        if ($newComment !== '') {
            $newComment .= "\n\n" . $metaBlock;
        } else {
            $newComment = $metaBlock;
        }
    }

    // Update status and comment in single query
    $update = "UPDATE customer_info SET audit_status = 'Application canceled', comment = :comment WHERE id = :id";
    $stmt2  = $db->prepare($update);
    $stmt2->bindParam(':comment', $newComment);
    $stmt2->bindParam(':id', $customerId, PDO::PARAM_INT);
    $stmt2->execute();

} catch (Exception $ex) {
    // Fail silently and still redirect back
}

header('Location: customerAccount.php?customerid=' . $customerId);
exit;

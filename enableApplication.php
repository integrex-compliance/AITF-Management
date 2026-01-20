
<!-- this file is crated by gaurav oli  -->

<?php
session_start();
include_once './dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
    header('Location: customerAccount.php');
    exit;
}

$customerId = (int)$_POST['customer_id'];

try {
    $sql = "SELECT audit_status, comment FROM customer_info WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $currentStatus = $row['audit_status'];
        $comment       = $row['comment'];

        $restoreStatus = '';

        // Try to restore from last recorded "Previous audit_status" marker in the comment
        if (!empty($comment)) {
            if (preg_match_all('/Previous audit_status:\s*([^\r\n]+)/', $comment, $matches) && !empty($matches[1])) {
                $restoreStatus = trim(end($matches[1]));
            }
        }

        // Avoid restoring to Application canceled again
        if ($restoreStatus === 'Application canceled') {
            $restoreStatus = '';
        }



// this section is created by gaurav oli 


        // If we still don't know, leave blank so user can choose from dropdown
        $updateStatus = $restoreStatus;

        $update = "UPDATE customer_info SET audit_status = :status WHERE id = :id";
        $stmt2  = $db->prepare($update);
        $stmt2->bindParam(':status', $updateStatus);
        $stmt2->bindParam(':id', $customerId, PDO::PARAM_INT);
        $stmt2->execute();
    }
} catch (Exception $ex) {
    // Silent fail, still redirect back
}

header('Location: customerAccount.php?customerid=' . $customerId);
exit;

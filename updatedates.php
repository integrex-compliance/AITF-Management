<?php
    include_once './dbConnect.php';

    function redirectWithAlert($message, $url)
    {
        echo '<script>';
        echo 'alert(' . json_encode($message) . ');';
        echo 'window.location.replace(' . json_encode($url) . ');';
        echo '</script>';
        exit;
    }

    if (!$db) {
        redirectWithAlert('Database connection failed. Please check local MySQL settings in dbConnect.php.', 'javascript:history.back()');
    }

    $due_date = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
    
    // Check if 'not applicable' is checked
    if (isset($_POST['sent_date_na']) && $_POST['sent_date_na'] == '1') {
        $sent_date = '0000-00-00';
    } else {
        $sent_date = isset($_POST['sent_date']) ? trim($_POST['sent_date']) : '';
    }
    $action_id = filter_input(INPUT_POST, 'action_id', FILTER_VALIDATE_INT);

    if (!$action_id || $due_date === '') {
        redirectWithAlert('Invalid date update request.', 'javascript:history.back()');
    }

    $dd = strtotime($due_date);
    if ($dd === false) {
        redirectWithAlert('Invalid due date.', 'javascript:history.back()');
    }

    $newdate = date('Y-m-d', $dd);
    $cust_id = null;

    try {
        $stmt = $db->prepare("SELECT * FROM `ca_service_dates` WHERE `action_id` = :action_id");
        $stmt->execute(array(':action_id' => $action_id));
        $dateRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dateRow) {
            redirectWithAlert('Date record not found.', 'javascript:history.back()');
        }

        $service_id = (int)$dateRow['service_id'];
        $action = $dateRow['action'];

        $stmt = $db->prepare("SELECT * FROM `service` WHERE `service_id` = :service_id");
        $stmt->execute(array(':service_id' => $service_id));
        $serviceRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$serviceRow) {
            redirectWithAlert('Service record not found.', 'javascript:history.back()');
        }

        $cust_id = (int)$serviceRow['customer_id'];

        // Reminder due dates must be after the latest CA CDC date for the customer.
        if (in_array($action, array('1st Reminder', '2nd Reminder', 'Expire Letter'), true)) {
            $stmt = $db->prepare(
                "SELECT crr.Date
                 FROM cdc_report_review crr
                 INNER JOIN service_type_ca ca ON crr.ca_service_id = ca.ca_id
                 INNER JOIN service s ON ca.service = s.service_id
                 WHERE s.customer_id = :cust_id AND s.service_type = 'CA'
                 ORDER BY crr.Date DESC
                 LIMIT 1"
            );
            $stmt->execute(array(':cust_id' => $cust_id));
            $caCdcDate = $stmt->fetchColumn();

            if ($caCdcDate && $newdate <= $caCdcDate) {
                redirectWithAlert(
                    'Error: ' . $action . ' date must be greater than CA CDC date (' . date('d.m.Y', strtotime($caCdcDate)) . ')',
                    'customerAccount.php?customerid=' . $cust_id
                );
            }
        }

        $stmt = $db->prepare(
            "UPDATE `ca_service_dates`
             SET `due_date` = :due_date, `sent_date` = :sent_date
             WHERE `action_id` = :action_id"
        );
        $stmt->execute(array(
            ':due_date' => $newdate,
            ':sent_date' => $sent_date,
            ':action_id' => $action_id
        ));

        redirectWithAlert('Dates Updated Successfully!!', 'customerAccount.php?customerid=' . $cust_id);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $redirectUrl = $cust_id ? 'customerAccount.php?customerid=' . $cust_id : 'javascript:history.back()';
        redirectWithAlert('Something Went Wrong', $redirectUrl);
    }
?>

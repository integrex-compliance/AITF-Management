<?php
include_once 'dbConnect.php';

function getCorrectStatus($db, $customer_id) {
    // Get all services
    $sql = "SELECT service_id, service_type, service_status, audit_date FROM service WHERE customer_id = :id AND service_type NOT IN ('CR') ORDER BY service_id ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $customer_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($services) == 0) return null;

    $status = 'Active'; 
    
    foreach ($services as $index => $svc) {
        $svcId = $svc['service_id'];
        
        // Is there a NEXT service with an audit_date?
        $nextSvcHasAudit = false;
        if (isset($services[$index + 1])) {
            $nextSvc = $services[$index + 1];
            if (!empty($nextSvc['audit_date']) && $nextSvc['audit_date'] != '0000-00-00') {
                $nextSvcHasAudit = true;
            }
        }
        
        if ($nextSvcHasAudit) {
            continue; 
        }
        
        $sqlDates = "SELECT action, due_date, sent_date FROM ca_service_dates WHERE service_id = :svcId";
        $stmtDates = $db->prepare($sqlDates);
        $stmtDates->execute([':svcId' => $svcId]);
        $dates = $stmtDates->fetchAll(PDO::FETCH_ASSOC);
        
        $today = date('Y-m-d');
        
        $isWithdraw = false;
        $isSuspend = false;
        $isExpire = false;
        
        foreach ($dates as $d) {
            $action = $d['action'];
            $due_date = $d['due_date'];
            $sent_date = $d['sent_date'];
            
            $isReached = false;
            if ($sent_date != '0000-00-00' && !empty($sent_date)) {
                $isReached = true;
            } else if ($due_date != '0000-00-00' && !empty($due_date) && $due_date <= $today) {
                $isReached = true;
            }
            
            if ($isReached) {
                if ($action == 'Withdraw Letter') {
                    $isWithdraw = true;
                } else if ($action == 'Suspend Letter') {
                    $isSuspend = true;
                } else if ($action == 'Expire Letter') {
                    $isExpire = true;
                }
            }
        }
        
        if ($isWithdraw) {
            $status = 'Withdrawn';
        } else if ($isExpire) {
            $status = 'Expired';
        } else if ($isSuspend) {
            $status = 'suspend';
        } else {
            $status = 'Active'; 
        }
        
        break;
    }
    
    return $status;
}

$customers = [581, 1133, 523, 1126];
foreach ($customers as $cid) {
    echo "Customer $cid calculated status: " . getCorrectStatus($db, $cid) . "\n";
}

?>

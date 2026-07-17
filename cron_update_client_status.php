<?php
// cron_update_client_status.php
include_once 'dbConnect.php';

function calculateRealClientStatus($db, $customer_id) {
    // Get all services excluding CR
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
        
        // If next service has audit done, current service's letters are superseded
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
            // Check if sent
            if ($sent_date != '0000-00-00' && !empty($sent_date)) {
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
            $status = 'Suspended';
        } else {
            $status = 'Active'; 
        }
        
        break; // Only evaluate the latest non-superseded service
    }
    
    return $status;
}

function updateAllClientStatuses($db) {
    echo "Starting client status update process...\n";
    
    // We get all clients that are not explicitly cancelled/etc., or just all clients.
    $sql = "SELECT id, audit_status, customer_name FROM customer_info WHERE audit_status NOT IN ('Application canceled')";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updatedCount = 0;
    
    foreach ($clients as $client) {
        $cid = $client['id'];
        $currentStatus = trim($client['audit_status']);
        
        $realStatus = calculateRealClientStatus($db, $cid);
        
        if ($realStatus !== null && $realStatus !== $currentStatus) {
            // Update customer_info
            $updateSql = "UPDATE customer_info SET audit_status = :status WHERE id = :id";
            $uStmt = $db->prepare($updateSql);
            $uStmt->execute([':status' => $realStatus, ':id' => $cid]);
            
            echo "Updated Customer ID $cid ({$client['customer_name']}) from '$currentStatus' to '$realStatus'\n";
            $updatedCount++;
        }
    }
    
    echo "Process completed. Total clients updated: $updatedCount\n";
}

if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    updateAllClientStatuses($db);
}
?>

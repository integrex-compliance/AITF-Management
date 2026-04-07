<?php
header('Content-Type: application/json');

// Mock API for testing Insert, Update, and Delete on IAF Dashboard

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $clientId = $_POST['client_id'] ?? '';
    
    // Validate
    if (empty($action) || empty($clientId)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing action or client ID']);
        exit;
    }
    
    // Simulated delay for realistic testing (e.g. 1 second)
    sleep(1);
    
    if ($action === 'insert') {
        $clientData = $_POST['client_data'] ?? [];
        
        // Mock insert: Log the complete data object instead of sending it over the internet so you can see it locally
        $logFile = __DIR__ . '/test_logs/api_insert_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        
        // Format the entire client data object beautifully as JSON
        $jsonData = json_encode([$clientData], JSON_PRETTY_PRINT);
        $logMessage = "[$timestamp] INSERT REQUEST RECEIVED -> Client ID: #$clientId\nFULL PAYLOAD:\n$jsonData\n\n---------------------------------------------\n";
        
        // Write it to the log file (Append mode)
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        echo json_encode(['status' => 'success', 'message' => "Successfully sent FULL data array format for Client #$clientId. Checked text file 'test_logs/api_insert_log.txt'!"]);
        exit;
        
    } elseif ($action === 'update') {
        // Real update logic: Fetch current data from DB and compare
        include_once './dbConnect.php';
        if (!isset($db)) {
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
            exit;
        }

        $clientData = $_POST['client_data'] ?? [];
        if (is_string($clientData)) $clientData = json_decode($clientData, true);

        // Fetch the fresh data from DB (using safe fallback logic as in dashboard)
        $query = "SELECT c.id as internal_client_id, c.customer_guid, c.customer_name, c.country, c.address, c.audit_status as certification_status, cp.certificate_id as certificate_tracking_id, cp.certificate_no as certificate_number, cp.registration_date as issue_date, cp.valid_until as expiry_date, (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name, c.schema_name FROM customer_info c LEFT JOIN certificate_print cp ON c.id = cp.customer_id WHERE c.id = :cid GROUP BY c.id ORDER BY c.id DESC LIMIT 1";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute([':cid' => $clientId]);
            $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $query = "SELECT c.id as internal_client_id, c.customer_name, c.address, c.audit_status as certification_status, cp.certificate_id as certificate_tracking_id, cp.certificate_no as certificate_number, cp.registration_date as issue_date, cp.valid_until as expiry_date, (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1) as standard_name FROM customer_info c LEFT JOIN certificate_print cp ON c.id = cp.customer_id WHERE c.id = :cid GROUP BY c.id ORDER BY c.id DESC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':cid' => $clientId]);
            $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$freshClient) {
            echo json_encode(['status' => 'error', 'message' => "Client #$clientId not found in database."]);
            exit;
        }

        // Apply same formatting logic as dashboard
        if(!isset($freshClient['customer_guid'])) $freshClient['customer_guid'] = 'GUID-' . str_pad($freshClient['internal_client_id'], 6, '0', STR_PAD_LEFT);
        if(!isset($freshClient['country'])) $freshClient['country'] = 'India';
        
        if(!isset($freshClient['schema_name']) || empty($freshClient['schema_name'])) {
            $std = $freshClient['standard_name'];
            if(strpos(strtoupper($std), '9001') !== false) $freshClient['schema_name'] = 'QMS';
            elseif(strpos(strtoupper($std), '14001') !== false) $freshClient['schema_name'] = 'EMS';
            elseif(strpos(strtoupper($std), '45001') !== false || strpos(strtoupper($std), '18001') !== false) $freshClient['schema_name'] = 'OHSMS';
            elseif(strpos(strtoupper($std), '27001') !== false) $freshClient['schema_name'] = 'ISMS';
            elseif(strpos(strtoupper($std), '22000') !== false) $freshClient['schema_name'] = 'FSMS';
            else $freshClient['schema_name'] = 'Other';
        }
        $freshClient['certification_type'] = $freshClient['certification_status'] ? 'Accredited' : 'Unaccredited';
        
        // Ensure values are strings for precise comparison (since DB might return ints/nulls)
        foreach($freshClient as $k => $v) {
            $freshClient[$k] = (string)$v;
        }
        foreach($clientData as $k => $v) {
            $clientData[$k] = (string)$v;
        }

        // Compare them
        $isUpdated = false;
        $differences = [];
        foreach($freshClient as $key => $val) {
            if(!isset($clientData[$key]) || $clientData[$key] !== $val) {
                $isUpdated = true;
                $differences[] = $key;
            }
        }

        if ($isUpdated) {
            echo json_encode([
                'status' => 'success', 
                'is_updated' => true,
                'new_data' => $freshClient,
                'title' => 'Updated Value Found!',
                'message' => "We found new changes in the database. The dashboard is syncing with the latest data."
            ]);
        } else {
            echo json_encode([
                'status' => 'info', 
                'is_updated' => false,
                'title' => 'No Updates Found',
                'message' => "No client value updated. The data is already up-to-date!"
            ]);
        }
        exit;
        
    } elseif ($action === 'delete') {
        // Mock delete: Delete the client
        echo json_encode(['status' => 'success', 'message' => "Client #$clientId was successfully deleted."]);
        exit;
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method. Note: This API accepts POST requests only.']);
exit;

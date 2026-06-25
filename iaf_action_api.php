


<?php
header('Content-Type: application/json');

require_once __DIR__ . '/include/iaf_certsearch_helper.php';

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
        if (is_string($clientData)) {
            $clientData = json_decode($clientData, true);
        }
        if (!is_array($clientData)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid client data payload.']);
            exit;
        }

        $iafPayload = iafCertSearchBuildPayload($clientData);
        $missingFields = iafCertSearchValidatePayload($iafPayload);
        if (!empty($missingFields)) {
            $response = [
                'status' => 'error',
                'message' => 'Missing required IAF CertSearch payload fields: ' . implode(', ', $missingFields),
                'request_payload' => $iafPayload
            ];
            iafCertSearchLogApiCall('insert_validation', 'POST', 'local-dashboard-validation', $iafPayload, 0, $response);
            echo json_encode($response);
            exit;
        }
        
        // Mock insert: Log the final IAF request payload instead of sending it over the internet.
        $logFile = __DIR__ . '/test_logs/api_insert_log.txt';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0775, true);
        }
        $timestamp = date('Y-m-d H:i:s');
        $mockResponse = [
            'status' => 'success',
            'message' => "Mock IAF CertSearch upload accepted for Client #$clientId.",
            'iaf_payload' => $iafPayload
        ];
        
        $jsonData = json_encode($iafPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $logMessage = "[$timestamp] INSERT REQUEST RECEIVED -> Client ID: #$clientId\nHTTP Code: 200\nFULL IAF REQUEST PAYLOAD:\n$jsonData\nAPI RESPONSE:\n" . json_encode($mockResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n---------------------------------------------\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        iafCertSearchLogApiCall('insert_mock', 'POST', 'local-dashboard-mock-upload-cert', $iafPayload, 200, $mockResponse);
        
        echo json_encode([
            'status' => 'success',
            'message' => "Successfully built IAF CertSearch payload for Client #$clientId. Check test_logs/api_insert_log.txt and logs/iaf_certsearch_api.log.",
            'http_code' => 200,
            'request_payload' => $iafPayload,
            'response' => $mockResponse
        ]);
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
        $query = "SELECT
            c.id as internal_client_id,
            c.customer_guid,
            c.customer_name,
            c.country,
            c.address,
            c.scope,
            c.audit_status as certification_status,
            cp.certificate_id as certificate_tracking_id,
            cp.certificate_no as certificate_number,
            cp.registration_date as certificate_issue_date,
            cp.registration_date as issue_date,
            cp.registration_date as original_issue_date,
            cp.valid_until as expiry_date,
            cp.valid_until as expire_date,
            cp.certificate_scope,
            COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
            COALESCE(
                (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                NULLIF(TRIM(aa.accereditation_requirement), ''),
                NULLIF(TRIM(aa.review_accredition), '')
            ) as accreditation_body_name,
            COALESCE(
                (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                NULLIF(TRIM(aa.accereditation_requirement), ''),
                NULLIF(TRIM(aa.review_accredition), '')
            ) as accreditation_body,
            COALESCE(
                (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                NULLIF(TRIM(aa.accereditation_requirement), '')
            ) as accreditation_requirement,
            COALESCE(NULLIF(aa.assessment_standard, ''), (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1)) as standard_name,
            c.schema_name,
            c.schema_name as scheme_name,
            aa.n_location as site
        FROM customer_info c
        LEFT JOIN certificate_print cp ON c.id = cp.customer_id
        LEFT JOIN service svc ON svc.customer_id = c.id AND svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = c.id)
        LEFT JOIN audit_application aa ON aa.uni_svc_id = svc.service_id OR aa.service_id = svc.service_id
        WHERE c.id = :cid
        GROUP BY c.id
        ORDER BY c.id DESC
        LIMIT 1";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute([':cid' => $clientId]);
            $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $query = "SELECT
                c.id as internal_client_id,
                c.customer_name,
                c.address,
                c.scope,
                c.audit_status as certification_status,
                cp.certificate_id as certificate_tracking_id,
                cp.certificate_no as certificate_number,
                cp.registration_date as certificate_issue_date,
                cp.registration_date as issue_date,
                cp.registration_date as original_issue_date,
                cp.valid_until as expiry_date,
                cp.valid_until as expire_date,
                cp.certificate_scope,
                COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
                COALESCE(
                    (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                    (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                    NULLIF(TRIM(aa.accereditation_requirement), ''),
                    NULLIF(TRIM(aa.review_accredition), '')
                ) as accreditation_body_name,
                COALESCE(
                    (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                    (SELECT NULLIF(TRIM(aa_req.review_accredition), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.review_accredition) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                    NULLIF(TRIM(aa.accereditation_requirement), ''),
                    NULLIF(TRIM(aa.review_accredition), '')
                ) as accreditation_body,
                COALESCE(
                    (SELECT NULLIF(TRIM(aa_req.accereditation_requirement), '') FROM audit_application aa_req WHERE aa_req.cust_id = c.id AND TRIM(aa_req.accereditation_requirement) <> '' ORDER BY aa_req.audit_id DESC LIMIT 1),
                    NULLIF(TRIM(aa.accereditation_requirement), '')
                ) as accreditation_requirement,
                COALESCE(NULLIF(aa.assessment_standard, ''), (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id ORDER BY asi.audit_schedule_id DESC LIMIT 1)) as standard_name,
                '' as schema_name,
                '' as scheme_name,
                aa.n_location as site
            FROM customer_info c
            LEFT JOIN certificate_print cp ON c.id = cp.customer_id
            LEFT JOIN service svc ON svc.customer_id = c.id AND svc.service_id = (SELECT MAX(tmp.service_id) FROM service tmp WHERE tmp.customer_id = c.id)
            LEFT JOIN audit_application aa ON aa.uni_svc_id = svc.service_id OR aa.service_id = svc.service_id
            WHERE c.id = :cid
            GROUP BY c.id
            ORDER BY c.id DESC
            LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':cid' => $clientId]);
            $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$freshClient) {
            echo json_encode(['status' => 'error', 'message' => "Client #$clientId not found in database."]);
            exit;
        }

        // Apply same formatting logic as dashboard
        $freshClient = iafCertSearchNormalizeClientData($freshClient);
        $freshClient['certification_scope'] = iafCertSearchFirstValue($freshClient, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
        $freshClient['scheme_name'] = iafCertSearchFirstValue($freshClient, ['scheme_name', 'schema_name']);
        $freshClient['accreditation_body'] = iafCertSearchFirstValue($freshClient, ['accreditation_body', 'accreditation_body_name', 'accreditation_requirement', 'accereditation_requirement']);
        $freshClient['certificate_issue_date'] = iafCertSearchFirstValue($freshClient, ['certificate_issue_date', 'issue_date']);
        $freshClient['expire_date'] = iafCertSearchFirstValue($freshClient, ['expire_date', 'expiry_date']);
        
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
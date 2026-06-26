


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
        
        include_once './dbConnect.php';
        if (!isset($db) || !$db instanceof PDO) {
            $response = [
                'status' => 'error',
                'message' => 'Database connection failed. IAF upload was not attempted.',
                'request_payload' => $iafPayload
            ];
            iafCertSearchLogApiCall('insert_db_error', 'POST', 'iaf-api-not-called', $iafPayload, 0, $response);
            echo json_encode($response);
            exit;
        }

        $apiResult = iafCertSearchSendPayload($iafPayload, 'insert');
        iafCertSearchLogApiCall(
            'insert',
            'POST',
            $apiResult['url'],
            $iafPayload,
            $apiResult['http_code'],
            $apiResult['response'] === null ? $apiResult['raw_response'] : $apiResult['response'],
            $apiResult['curl_error']
        );

        try {
            iafCertSearchSaveSyncResult($db, $clientId, 'insert', $iafPayload, $apiResult);
        } catch (Exception $e) {
            $apiResult['success'] = false;
            $apiResult['error_message'] = trim($apiResult['error_message'] . ' Database sync save failed: ' . $e->getMessage());
            iafCertSearchLogApiCall('insert_db_save_error', 'POST', $apiResult['url'], $iafPayload, $apiResult['http_code'], $apiResult, $e->getMessage());
        }

        if ($apiResult['success']) {
            echo json_encode([
                'status' => 'success',
                'message' => "Successfully sent IAF CertSearch payload for Client #$clientId.",
                'http_code' => $apiResult['http_code'],
                'iaf_record_id' => $apiResult['iaf_record_id'],
                'request_payload' => $iafPayload,
                'response' => $apiResult['response']
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => "IAF API upload failed for Client #$clientId: " . $apiResult['error_message'],
            'http_code' => $apiResult['http_code'],
            'iaf_record_id' => $apiResult['iaf_record_id'],
            'request_payload' => $iafPayload,
            'response' => $apiResult['response'],
            'raw_response' => $apiResult['raw_response'],
            'curl_error' => $apiResult['curl_error']
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

        // Get the service_id from client data (service-level query)
        $svcId = $clientData['svc_id'] ?? '';
        $officeUseId = isset($clientData['cdc_office_use_id']) ? (int) $clientData['cdc_office_use_id'] : 0;

        // Fetch the fresh data from DB using service-level query (matching dashboard)
        if (!empty($svcId)) {
            // Service-level: fetch per-service data
            $query = "SELECT
                c.id as internal_client_id,
                c.customer_name,
                'India' as country,
                c.address,
                c.scope,
                svc.service_id as svc_id,
                svc.service_type as svc_type,
                cou.id as cdc_office_use_id,
                cou.iso_index as iso_index,
                COALESCE(
                    NULLIF(TRIM(cdc.cdc_status), ''),
                    NULLIF(TRIM(svc.service_status), ''),
                    c.audit_status
                ) as certification_status,
                cp.certificate_id as certificate_tracking_id,
                COALESCE(NULLIF(cou.certificate_no, ''), NULLIF(cp.certificate_no, ''), NULLIF(cdc.certificateNo, '')) as certificate_number,
                COALESCE(cou.rev_date, cp.registration_date, cdc.Date) as certificate_issue_date,
                COALESCE(cou.rev_date, cp.registration_date, cdc.Date) as issue_date,
                COALESCE(cou.rev_date, cp.registration_date, cdc.Date) as original_issue_date,
                COALESCE(cou.expire_date, cp.valid_until, cdc.expiredate) as expiry_date,
                COALESCE(cou.expire_date, cp.valid_until, cdc.expiredate) as expire_date,
                cp.certificate_scope,
                COALESCE(NULLIF(aa.proposed_scope, ''), cp.certificate_scope, c.scope) as certification_scope,
                COALESCE(
                    NULLIF(TRIM(aa.accereditation_requirement), ''),
                    NULLIF(TRIM(aa.review_accredition), '')
                ) as accreditation_body_name,
                COALESCE(
                    NULLIF(TRIM(aa.accereditation_requirement), ''),
                    NULLIF(TRIM(aa.review_accredition), '')
                ) as accreditation_body,
                NULLIF(TRIM(aa.accereditation_requirement), '') as accreditation_requirement,
                COALESCE(
                    NULLIF(cou.iso_label, ''),
                    NULLIF(aa.assessment_standard, ''),
                    (SELECT asi.schedule_app_standard FROM audit_schedule_id asi WHERE asi.customer_ca_id = c.id AND asi.serviceca_id = stca.ca_id ORDER BY asi.audit_schedule_id DESC LIMIT 1),
                    (SELECT asi2.schedule_app_standard FROM audit_schedule_id asi2 WHERE asi2.customer_ca_id = c.id ORDER BY asi2.audit_schedule_id DESC LIMIT 1)
                ) as standard_name,
                '' as schema_name,
                '' as scheme_name,
                aa.n_location as site
            FROM customer_info c
            INNER JOIN service svc ON svc.customer_id = c.id AND svc.service_id = :svc_id
            LEFT JOIN certificate_print cp ON cp.service_id = svc.service_id
                AND cp.certificate_id = (
                    SELECT MAX(cp_s.certificate_id) FROM certificate_print cp_s WHERE cp_s.service_id = svc.service_id
                )
            LEFT JOIN service_type_ca stca ON stca.service = svc.service_id
            LEFT JOIN cdc_report_review cdc ON cdc.ca_service_id = stca.ca_id
                AND cdc.customer_id = c.id
                AND cdc.crr_id = (
                    SELECT MAX(cdc_s.crr_id) FROM cdc_report_review cdc_s
                    WHERE cdc_s.ca_service_id = stca.ca_id AND cdc_s.customer_id = c.id
                )
            LEFT JOIN cdc_report_review_office_use cou ON cou.crr_id = cdc.crr_id
            LEFT JOIN audit_application aa ON aa.audit_id = (
                SELECT MAX(aa_s.audit_id) FROM audit_application aa_s
                WHERE aa_s.uni_svc_id = svc.service_id OR aa_s.service_id = svc.service_id OR aa_s.service_id = stca.ca_id
            )
            WHERE c.id = :cid
                AND (:office_use_id_filter = 0 OR cou.id = :office_use_id_match)
            ORDER BY cou.iso_index ASC
            LIMIT 1";
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':cid' => $clientId, ':svc_id' => $svcId, ':office_use_id_filter' => $officeUseId, ':office_use_id_match' => $officeUseId]);
                $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $freshClient = null;
                error_log("IAF Update service-level query failed: " . $e->getMessage());
            }
        } else {
            $freshClient = null;
        }

        // Fallback: client without service (no svc_id)
        if (!$freshClient) {
            $query = "SELECT
                c.id as internal_client_id,
                c.customer_name,
                'India' as country,
                c.address,
                c.scope,
                '' as svc_id,
                '' as svc_type,
                '' as cdc_office_use_id,
                '' as iso_index,
                c.audit_status as certification_status,
                '' as certificate_tracking_id,
                '' as certificate_number,
                '' as certificate_issue_date,
                '' as issue_date,
                '' as original_issue_date,
                '' as expiry_date,
                '' as expire_date,
                '' as certificate_scope,
                c.scope as certification_scope,
                '' as accreditation_body_name,
                '' as accreditation_body,
                '' as accreditation_requirement,
                '' as standard_name,
                '' as schema_name,
                '' as scheme_name,
                '' as site
            FROM customer_info c
            WHERE c.id = :cid
            LIMIT 1";
            try {
                $stmt = $db->prepare($query);
                $stmt->execute([':cid' => $clientId]);
                $freshClient = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("IAF Update fallback query failed: " . $e->getMessage());
            }
        }

        if (!$freshClient) {
            echo json_encode(['status' => 'error', 'message' => "Client #$clientId not found in database."]);
            exit;
        }

        // Apply same formatting logic as dashboard
        $freshClient = iafCertSearchNormalizeClientData($freshClient);
        $freshClient['certification_scope'] = iafCertSearchFirstValue($freshClient, ['certification_scope', 'proposed_scope', 'certificate_scope', 'scope']);
        $freshClient['scheme_name'] = iafCertSearchFirstValue($freshClient, ['scheme_name', 'schema_name']);
        $freshClient['accreditation_body'] = iafCertSearchNormalizeAccreditationBodyName(iafCertSearchFirstValue($freshClient, ['accreditation_body', 'accreditation_body_name', 'accreditation_requirement', 'accereditation_requirement'], 'NABCB'));
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

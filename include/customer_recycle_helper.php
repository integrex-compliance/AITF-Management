<?php
/**
 * Helper library for Client and Service Recycle Bin (Soft Delete / Restore / Permanent Delete)
 */

if (!function_exists('aitfEnsureRecycleSchema')) {
    function aitfEnsureRecycleSchema($db) {
        if (!$db) return;
        static $checked = false;
        if ($checked) return;

        try {
            $cols = $db->query("SHOW COLUMNS FROM service LIKE 'is_deleted'")->fetchAll();
            if (empty($cols)) {
                $db->exec("ALTER TABLE service ADD COLUMN is_deleted VARCHAR(10) DEFAULT 'NO'");
                $db->exec("UPDATE service SET is_deleted = 'NO' WHERE is_deleted IS NULL");
            }

            $colsDate = $db->query("SHOW COLUMNS FROM service LIKE 'deleted_date'")->fetchAll();
            if (empty($colsDate)) {
                $db->exec("ALTER TABLE service ADD COLUMN deleted_date DATETIME NULL");
            }
        } catch (Exception $e) {
            error_log("Recycle Schema Migration Error: " . $e->getMessage());
        }
        $checked = true;
    }
}

if (!function_exists('aitfSoftDeleteService')) {
    function aitfSoftDeleteService($db, $serviceId, $customerId = null, $serviceType = null) {
        if (!$db || empty($serviceId)) return false;
        aitfEnsureRecycleSchema($db);

        // Fetch service info if not provided
        if (empty($customerId) || empty($serviceType)) {
            $stmt = $db->prepare("SELECT customer_id, service_type FROM service WHERE service_id = :id");
            $stmt->execute([':id' => $serviceId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $customerId = $row['customer_id'];
                $serviceType = $row['service_type'];
            }
        }

        // Mark service as deleted
        $stmt = $db->prepare("UPDATE service SET is_deleted = 'YES', deleted_date = NOW() WHERE service_id = :id");
        $stmt->execute([':id' => $serviceId]);

        // Update customer's next_service if applicable
        if (!empty($customerId) && !empty($serviceType)) {
            $nextMap = [
                'CA' => 'CA',
                'PTR' => 'PTR',
                'CR' => 'CR',
                'SA-I' => 'SA-I',
                'SA-II' => 'SA-II',
                'RC' => 'RC',
                'SP' => 'SP',
                'TA' => 'TA'
            ];
            $next = isset($nextMap[$serviceType]) ? $nextMap[$serviceType] : $serviceType;
            $stmtNext = $db->prepare("UPDATE customer_info SET next_service = :next WHERE id = :cid");
            $stmtNext->execute([':next' => $next, ':cid' => $customerId]);

            // Decrement active service_count if > 0
            $db->prepare("UPDATE customer_info SET service_count = service_count - 1 WHERE id = :cid AND service_count > 0")
               ->execute([':cid' => $customerId]);
        }

        return true;
    }
}

if (!function_exists('aitfRestoreService')) {
    function aitfRestoreService($db, $serviceId) {
        if (!$db || empty($serviceId)) return false;
        aitfEnsureRecycleSchema($db);

        // Restore service
        $stmt = $db->prepare("UPDATE service SET is_deleted = 'NO', deleted_date = NULL WHERE service_id = :id");
        $stmt->execute([':id' => $serviceId]);

        // Fetch customer_id to update next_service
        $stmtCust = $db->prepare("SELECT customer_id, service_type FROM service WHERE service_id = :id");
        $stmtCust->execute([':id' => $serviceId]);
        $svc = $stmtCust->fetch(PDO::FETCH_ASSOC);

        if ($svc && !empty($svc['customer_id'])) {
            $cid = $svc['customer_id'];
            // Increment active service_count
            $db->prepare("UPDATE customer_info SET service_count = COALESCE(service_count, 0) + 1 WHERE id = :cid")
               ->execute([':cid' => $cid]);

            // Update next_service based on latest active service
            $stmtLatest = $db->prepare("SELECT service_type FROM service WHERE customer_id = :cid AND (is_deleted = 'NO' OR is_deleted IS NULL) ORDER BY service_id DESC LIMIT 1");
            $stmtLatest->execute([':cid' => $cid]);
            $latest = $stmtLatest->fetch(PDO::FETCH_ASSOC);
            if ($latest) {
                $seq = ['CA' => 'SA-I', 'SA-I' => 'SA-II', 'SA-II' => 'RC', 'RC' => 'SA-I'];
                if (isset($seq[$latest['service_type']])) {
                    $db->prepare("UPDATE customer_info SET next_service = :next WHERE id = :cid")
                       ->execute([':next' => $seq[$latest['service_type']], ':cid' => $cid]);
                }
            }
        }

        return true;
    }
}

if (!function_exists('aitfHardDeleteCA')) {
    function aitfHardDeleteCA($db, $caId, $customerId, $serviceId) {
        if ($caId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $audit1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($audit1Id) {
                $db->prepare("DELETE FROM audit_schedule WHERE audit_scheduleid = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
                $db->prepare("DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
            }

            $st = $db->prepare("SELECT audit_stageii_id FROM stage_twoaudit_id WHERE service_id = :service AND customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $stage2Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Id) {
                $db->prepare("DELETE FROM stage_audit_two WHERE audit_stageii_id = :id")->execute([':id' => $stage2Id['audit_stageii_id']]);
                $db->prepare("DELETE FROM stage_twoaudit_id WHERE audit_stageii_id = :id")->execute([':id' => $stage2Id['audit_stageii_id']]);
            }

            $st = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = :service and ca_customer = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $audit2Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($audit2Id) {
                $db->prepare("DELETE FROM audit_scheduleii WHERE scheduleii_id = :id")->execute([':id' => $audit2Id['audit_scheduleii_id']]);
                $db->prepare("DELETE FROM audit_scheduleii_id WHERE audit_scheduleii_id = :id")->execute([':id' => $audit2Id['audit_scheduleii_id']]);
            }

            $st = $db->prepare("SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $stage1Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Annexid) {
                $db->prepare("DELETE FROM stage_i_audit WHERE ca_service_audit = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
                $db->prepare("DELETE FROM stage_i_audit_id WHERE stage_i_id = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
            }

            $st = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $stage2Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Annexid) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
                $db->prepare("DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
            }

            $st = $db->prepare("SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $cdc_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cdc_id) {
                $db->prepare("DELETE FROM cdc_report_review WHERE crr_id = :id")->execute([':id' => $cdc_id['crr_id']]);
            }

            $st = $db->prepare("SELECT certificate_id FROM certificate_print where service_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $cert_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cert_id) {
                $db->prepare("DELETE FROM certificate_print WHERE certificate_id = :id")->execute([':id' => $cert_id['certificate_id']]);
            }

            $st = $db->prepare("SELECT control_id FROM control_formati_id WHERE caservice_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $cf1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cf1Id) {
                $db->prepare("DELETE FROM control_formati WHERE control_id = :id")->execute([':id' => $cf1Id['control_id']]);
                $db->prepare("DELETE FROM control_formati_id WHERE control_id = :id")->execute([':id' => $cf1Id['control_id']]);
            }

            $st = $db->prepare("SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $customerSurvayId = $st->fetch(PDO::FETCH_ASSOC);
            if ($customerSurvayId) {
                $db->prepare("DELETE FROM customer_survey WHERE survayid = :id")->execute([':id' => $customerSurvayId['survey_id']]);
                $db->prepare("DELETE FROM customer_survey_id WHERE survey_id = :id")->execute([':id' => $customerSurvayId['survey_id']]);
            }

            $st = $db->prepare("SELECT control2_id FROM control_formatii_id WHERE caservice_id = :service and customer_id = :customer");
            $st->execute([':service' => $caId, ':customer' => $customerId]);
            $cf2Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cf2Id) {
                $db->prepare("DELETE FROM control_formatii WHERE control2_id = :id")->execute([':id' => $cf2Id['control2_id']]);
                $db->prepare("DELETE FROM control_formatii_id WHERE control2_id = :id")->execute([':id' => $cf2Id['control2_id']]);
            }

            $db->prepare("DELETE FROM service_type_ca WHERE ca_id = :service")->execute([':service' => $caId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeletePTR')) {
    function aitfHardDeletePTR($db, $ptrId, $customerId, $serviceId) {
        if ($ptrId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $ptrId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT servelance_id FROM servelance_1_id where service_id = :id");
            $st->execute([':id' => $ptrId]);
            $servelance = $st->fetch(PDO::FETCH_ASSOC);
            if ($servelance) {
                $db->prepare("DELETE FROM servelance_audit WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
                $db->prepare("DELETE FROM servelance_1_id WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
            }

            $st = $db->prepare("SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer ");
            $st->execute([':service' => $ptrId, ':customer' => $customerId]);
            $stage1Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Annexid) {
                $db->prepare("DELETE FROM stage_i_audit WHERE ca_service_audit = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
                $db->prepare("DELETE FROM stage_i_audit_id WHERE stage_i_id = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
            }

            $db->prepare("DELETE FROM service_type_ptr WHERE prt_id = :id")->execute([':id' => $ptrId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteCR')) {
    function aitfHardDeleteCR($db, $crId, $customerId, $serviceId) {
        if ($crId) {
            $st = $db->prepare("SELECT audit_id FROM audit_application WHERE service_id = :service");
            $st->execute([':service' => $crId]);
            $auditAppId = $st->fetch(PDO::FETCH_ASSOC);
            if ($auditAppId) {
                $db->prepare("DELETE FROM audit_application WHERE audit_id = :auditid")->execute([':auditid' => $auditAppId['audit_id']]);
            }

            $st = $db->prepare("SELECT contractid FROM contract_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $crId, ':customer' => $customerId]);
            $contractId = $st->fetch(PDO::FETCH_ASSOC);
            if ($contractId) {
                $db->prepare("DELETE FROM contract_review WHERE ca_service_contract = :contractid")->execute([':contractid' => $contractId['contractid']]);
                $db->prepare("DELETE FROM contract_id WHERE contractid = :id")->execute([':id' => $contractId['contractid']]);
            }

            $st = $db->prepare("SELECT id FROM certification_aggrement WHERE service = :id");
            $st->execute([':id' => $crId]);
            $certId = $st->fetch(PDO::FETCH_ASSOC);
            if ($certId) {
                $db->prepare("DELETE FROM certification_aggrement WHERE id = :id")->execute([':id' => $certId['id']]);
            }

            $st = $db->prepare("SELECT assessment_id FROM assessment_planning WHERE service_id = :id");
            $st->execute([':id' => $crId]);
            $assessmentId = $st->fetch(PDO::FETCH_ASSOC);
            if ($assessmentId) {
                $db->prepare("DELETE FROM assessment_planning WHERE assessment_id = :id")->execute([':id' => $assessmentId['assessment_id']]);
            }

            $db->prepare("DELETE FROM service_type_cr WHERE cr_id = :id")->execute([':id' => $crId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteSAI')) {
    function aitfHardDeleteSAI($db, $saiId, $customerId, $serviceId) {
        if ($saiId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $saiId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT servelance_id FROM servelance_1_id WHERE service_id = :service");
            $st->execute([':service' => $saiId]);
            $servelance = $st->fetch(PDO::FETCH_ASSOC);
            if ($servelance) {
                $db->prepare("DELETE FROM servelance_audit WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
                $db->prepare("DELETE FROM servelance_1_id WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
            }

            $st = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiId, ':customer' => $customerId]);
            $stage2Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Annexid) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
                $db->prepare("DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
            }

            $st = $db->prepare("SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiId, ':customer' => $customerId]);
            $cdc_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cdc_id) {
                $db->prepare("DELETE FROM cdc_report_review WHERE crr_id = :id")->execute([':id' => $cdc_id['crr_id']]);
            }

            $st = $db->prepare("SELECT certificate_id FROM certificate_print where service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiId, ':customer' => $customerId]);
            $cert_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cert_id) {
                $db->prepare("DELETE FROM certificate_print WHERE certificate_id = :id")->execute([':id' => $cert_id['certificate_id']]);
            }

            $st = $db->prepare("SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer");
            $st->execute([':service' => $saiId, ':customer' => $customerId]);
            $customerSurvayId = $st->fetch(PDO::FETCH_ASSOC);
            if ($customerSurvayId) {
                $db->prepare("DELETE FROM customer_survey WHERE survayid = :id")->execute([':id' => $customerSurvayId['survey_id']]);
                $db->prepare("DELETE FROM customer_survey_id WHERE survey_id = :id")->execute([':id' => $customerSurvayId['survey_id']]);
            }

            $db->prepare("DELETE FROM service_type_sai WHERE sai_id = :service")->execute([':service' => $saiId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteSAII')) {
    function aitfHardDeleteSAII($db, $saiiId, $customerId, $serviceId) {
        if ($saiiId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $saiiId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT servelance_id FROM servelance_1_id WHERE service_id = :service");
            $st->execute([':service' => $saiiId]);
            $servelance = $st->fetch(PDO::FETCH_ASSOC);
            if ($servelance) {
                $db->prepare("DELETE FROM servelance_audit WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
                $db->prepare("DELETE FROM servelance_1_id WHERE servelance_id = :id")->execute([':id' => $servelance['servelance_id']]);
            }

            $st = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiiId, ':customer' => $customerId]);
            $stage2Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Annexid) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
                $db->prepare("DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
            }

            $st = $db->prepare("SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiiId, ':customer' => $customerId]);
            $cdc_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cdc_id) {
                $db->prepare("DELETE FROM cdc_report_review WHERE crr_id = :id")->execute([':id' => $cdc_id['crr_id']]);
            }

            $st = $db->prepare("SELECT certificate_id FROM certificate_print where service_id = :service and customer_id = :customer");
            $st->execute([':service' => $saiiId, ':customer' => $customerId]);
            $cert_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cert_id) {
                $db->prepare("DELETE FROM certificate_print WHERE certificate_id = :id")->execute([':id' => $cert_id['certificate_id']]);
            }

            $st = $db->prepare("SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer");
            $st->execute([':service' => $saiiId, ':customer' => $customerId]);
            $customerSurvayId = $st->fetch(PDO::FETCH_ASSOC);
            if ($customerSurvayId) {
                $db->prepare("DELETE FROM customer_survey WHERE survayid = :id")->execute([':id' => $customerSurvayId['survey_id']]);
                $db->prepare("DELETE FROM customer_survey_id WHERE survey_id = :id")->execute([':id' => $customerSurvayId['survey_id']]);
            }

            $db->prepare("DELETE FROM service_type_saii WHERE saii_id = :service")->execute([':service' => $saiiId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteRC')) {
    function aitfHardDeleteRC($db, $rcId, $customerId, $serviceId) {
        if ($rcId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_service = :service and ca_customer = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $audit2Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($audit2Id) {
                $db->prepare("DELETE FROM audit_scheduleii WHERE scheduleii_id = :id")->execute([':id' => $audit2Id['audit_scheduleii_id']]);
                $db->prepare("DELETE FROM audit_scheduleii_id WHERE audit_scheduleii_id = :id")->execute([':id' => $audit2Id['audit_scheduleii_id']]);
            }

            $st = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $stage2Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Annexid) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
                $db->prepare("DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
            }

            $st = $db->prepare("SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $cdc_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cdc_id) {
                $db->prepare("DELETE FROM cdc_report_review WHERE crr_id = :id")->execute([':id' => $cdc_id['crr_id']]);
            }

            $st = $db->prepare("SELECT certificate_id FROM certificate_print where service_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $cert_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cert_id) {
                $db->prepare("DELETE FROM certificate_print WHERE certificate_id = :id")->execute([':id' => $cert_id['certificate_id']]);
            }

            $st = $db->prepare("SELECT control_id FROM control_formati_id WHERE caservice_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $cf1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cf1Id) {
                $db->prepare("DELETE FROM control_formati WHERE control_id = :id")->execute([':id' => $cf1Id['control_id']]);
                $db->prepare("DELETE FROM control_formati_id WHERE control_id = :id")->execute([':id' => $cf1Id['control_id']]);
            }

            $st = $db->prepare("SELECT survey_id FROM customer_survey_id WHERE ca_serviceid = :service and customer_ca = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $customerSurvayId = $st->fetch(PDO::FETCH_ASSOC);
            if ($customerSurvayId) {
                $db->prepare("DELETE FROM customer_survey WHERE survayid = :id")->execute([':id' => $customerSurvayId['survey_id']]);
                $db->prepare("DELETE FROM customer_survey_id WHERE survey_id = :id")->execute([':id' => $customerSurvayId['survey_id']]);
            }

            $st = $db->prepare("SELECT control2_id FROM control_formatii_id WHERE caservice_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $cf2Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cf2Id) {
                $db->prepare("DELETE FROM control_formatii WHERE control2_id = :id")->execute([':id' => $cf2Id['control2_id']]);
                $db->prepare("DELETE FROM control_formatii_id WHERE control2_id = :id")->execute([':id' => $cf2Id['control2_id']]);
            }

            $st = $db->prepare("SELECT audit_id FROM audit_application WHERE service_id = :service ");
            $st->execute([':service' => $rcId]);
            $auditAppId = $st->fetch(PDO::FETCH_ASSOC);
            if ($auditAppId) {
                $db->prepare("DELETE FROM audit_application WHERE audit_id = :auditid")->execute([':auditid' => $auditAppId['audit_id']]);
            }

            $st = $db->prepare("SELECT contractid FROM contract_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $rcId, ':customer' => $customerId]);
            $contractId = $st->fetch(PDO::FETCH_ASSOC);
            if ($contractId) {
                $db->prepare("DELETE FROM contract_review WHERE ca_service_contract = :contractid")->execute([':contractid' => $contractId['contractid']]);
                $db->prepare("DELETE FROM contract_id WHERE contractid = :id")->execute([':id' => $contractId['contractid']]);
            }

            $st = $db->prepare("SELECT id FROM certification_aggrement WHERE service = :id");
            $st->execute([':id' => $rcId]);
            $certId = $st->fetch(PDO::FETCH_ASSOC);
            if ($certId) {
                $db->prepare("DELETE FROM certification_aggrement WHERE id = :id")->execute([':id' => $certId['id']]);
            }

            $st = $db->prepare("SELECT assessment_id FROM assessment_planning WHERE service_id = :id");
            $st->execute([':id' => $rcId]);
            $assessmentId = $st->fetch(PDO::FETCH_ASSOC);
            if ($assessmentId) {
                $db->prepare("DELETE FROM assessment_planning WHERE assessment_id = :id")->execute([':id' => $assessmentId['assessment_id']]);
            }

            $db->prepare("DELETE FROM service_type_rc WHERE rc_id = :service")->execute([':service' => $rcId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteSP')) {
    function aitfHardDeleteSP($db, $spId, $customerId, $serviceId) {
        if ($spId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $spId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer");
            $st->execute([':service' => $spId, ':customer' => $customerId]);
            $audit1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($audit1Id) {
                $db->prepare("DELETE FROM audit_schedule WHERE audit_scheduleid = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
                $db->prepare("DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
            }

            $st = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $spId, ':customer' => $customerId]);
            $stage2Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage2Annexid) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
                $db->prepare("DELETE FROM stage_ii_audit_id WHERE stage_ii_id = :id")->execute([':id' => $stage2Annexid['stage_ii_id']]);
            }

            $st = $db->prepare("SELECT crr_id FROM cdc_report_review WHERE ca_service_id = :service and customer_id = :customer");
            $st->execute([':service' => $spId, ':customer' => $customerId]);
            $cdc_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cdc_id) {
                $db->prepare("DELETE FROM cdc_report_review WHERE crr_id = :id")->execute([':id' => $cdc_id['crr_id']]);
            }

            $st = $db->prepare("SELECT certificate_id FROM certificate_print where service_id = :service and customer_id = :customer");
            $st->execute([':service' => $spId, ':customer' => $customerId]);
            $cert_id = $st->fetch(PDO::FETCH_ASSOC);
            if ($cert_id) {
                $db->prepare("DELETE FROM certificate_print WHERE certificate_id = :id")->execute([':id' => $cert_id['certificate_id']]);
            }

            $st = $db->prepare("SELECT id FROM certification_aggrement WHERE service = :id");
            $st->execute([':id' => $spId]);
            $certId = $st->fetch(PDO::FETCH_ASSOC);
            if ($certId) {
                $db->prepare("DELETE FROM certification_aggrement WHERE id = :id")->execute([':id' => $certId['id']]);
            }

            $db->prepare("DELETE FROM service_type_sp WHERE sp_id = :service")->execute([':service' => $spId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfHardDeleteTA')) {
    function aitfHardDeleteTA($db, $taId, $customerId, $serviceId) {
        if ($taId) {
            $st = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_service_id = :service and ca_customer_id = :customer ");
            $st->execute([':service' => $taId, ':customer' => $customerId]);
            $stage1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
                $db->prepare("DELETE FROM stage_oneaudit_id WHERE audit_stage_id = :id")->execute([':id' => $stage1Id['audit_stage_id']]);
            }

            $st = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE serviceca_id = :service and customer_ca_id = :customer");
            $st->execute([':service' => $taId, ':customer' => $customerId]);
            $audit1Id = $st->fetch(PDO::FETCH_ASSOC);
            if ($audit1Id) {
                $db->prepare("DELETE FROM audit_schedule WHERE audit_scheduleid = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
                $db->prepare("DELETE FROM audit_schedule_id WHERE audit_schedule_id = :id")->execute([':id' => $audit1Id['audit_schedule_id']]);
            }

            $st = $db->prepare("SELECT stage_i_id FROM stage_i_audit_id WHERE ca_service_id = :service and customer_id = :customer ");
            $st->execute([':service' => $taId, ':customer' => $customerId]);
            $stage1Annexid = $st->fetch(PDO::FETCH_ASSOC);
            if ($stage1Annexid) {
                $db->prepare("DELETE FROM stage_i_audit WHERE ca_service_audit = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
                $db->prepare("DELETE FROM stage_i_audit_id WHERE stage_i_id = :id")->execute([':id' => $stage1Annexid['stage_i_id']]);
            }

            $db->prepare("DELETE FROM service_type_ta WHERE ta_id = :service")->execute([':service' => $taId]);
        }

        if ($serviceId) {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :service_id")->execute([':service_id' => $serviceId]);
        }
    }
}

if (!function_exists('aitfPermanentlyDeleteService')) {
    function aitfPermanentlyDeleteService($db, $serviceId) {
        if (!$db || empty($serviceId)) return false;

        $stmt = $db->prepare("SELECT service_id, service_type, customer_id FROM service WHERE service_id = :id");
        $stmt->execute([':id' => $serviceId]);
        $svc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$svc) return false;

        $serviceType = strtoupper(trim($svc['service_type']));
        $customerId = $svc['customer_id'];

        if ($serviceType === 'CA') {
            $st = $db->prepare("SELECT ca_id FROM service_type_ca WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $caId = $st->fetchColumn();
            aitfHardDeleteCA($db, $caId, $customerId, $serviceId);
        } else if ($serviceType === 'SA-I' || $serviceType === 'SAI') {
            $st = $db->prepare("SELECT sai_id FROM service_type_sai WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $saiId = $st->fetchColumn();
            aitfHardDeleteSAI($db, $saiId, $customerId, $serviceId);
        } else if ($serviceType === 'SA-II' || $serviceType === 'SAII') {
            $st = $db->prepare("SELECT saii_id FROM service_type_saii WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $saiiId = $st->fetchColumn();
            aitfHardDeleteSAII($db, $saiiId, $customerId, $serviceId);
        } else if ($serviceType === 'RC') {
            $st = $db->prepare("SELECT rc_id FROM service_type_rc WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $rcId = $st->fetchColumn();
            aitfHardDeleteRC($db, $rcId, $customerId, $serviceId);
        } else if ($serviceType === 'CR') {
            $st = $db->prepare("SELECT cr_id FROM service_type_cr WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $crId = $st->fetchColumn();
            aitfHardDeleteCR($db, $crId, $customerId, $serviceId);
        } else if ($serviceType === 'PTR') {
            $st = $db->prepare("SELECT prt_id FROM service_type_ptr WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $ptrId = $st->fetchColumn();
            aitfHardDeletePTR($db, $ptrId, $customerId, $serviceId);
        } else if ($serviceType === 'SP') {
            $st = $db->prepare("SELECT sp_id FROM service_type_sp WHERE service = :sid");
            $st->execute([':sid' => $serviceId]);
            $spId = $st->fetchColumn();
            aitfHardDeleteSP($db, $spId, $customerId, $serviceId);
        } else if ($serviceType === 'TA') {
            $st = $db->prepare("SELECT ta_id FROM service_type_ta WHERE service_id = :sid");
            $st->execute([':sid' => $serviceId]);
            $taId = $st->fetchColumn();
            aitfHardDeleteTA($db, $taId, $customerId, $serviceId);
        } else {
            $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :sid")->execute([':sid' => $serviceId]);
            $db->prepare("DELETE FROM service WHERE service_id = :sid")->execute([':sid' => $serviceId]);
        }

        return true;
    }
}

if (!function_exists('aitfPermanentlyDeleteClient')) {
    function aitfPermanentlyDeleteClient($db, $clientId) {
        if (!$db || empty($clientId)) return false;
        try {
            $tablesSimple = [
                'allotment_count' => 'client_id',
                'assessment_planning' => 'customer_id',
                'cdc_report_review' => 'customer_id',
                'certificate_print' => 'customer_id',
                'service_type_cr' => 'customer_id',
                'service_type_ca' => 'customer_id',
                'service_type_sai' => 'customer_id',
                'service_type_saii' => 'customer_id',
                'service_type_rc' => 'rc_customer',
                'service_type_sp' => 'sp_customer',
                'service_type_ptr' => 'customer_id',
            ];

            // 1. Audit application & ca_service_dates from service
            $stServices = $db->prepare("SELECT service_id FROM service WHERE customer_id = :id");
            $stServices->execute([':id' => $clientId]);
            $svcs = $stServices->fetchAll(PDO::FETCH_COLUMN);
            foreach ($svcs as $sid) {
                $stAudit = $db->prepare("SELECT audit_id FROM audit_application WHERE uni_svc_id = :sid");
                $stAudit->execute([':sid' => $sid]);
                $aId = $stAudit->fetchColumn();
                if ($aId) {
                    $db->prepare("DELETE FROM audit_iaf_nace WHERE audit_id = :id")->execute([':id' => $aId]);
                }
                $db->prepare("DELETE FROM audit_application WHERE uni_svc_id = :sid")->execute([':sid' => $sid]);
                $db->prepare("DELETE FROM ca_service_dates WHERE service_id = :sid")->execute([':sid' => $sid]);
            }

            // 2. Audit schedule 1
            $stSch1 = $db->prepare("SELECT audit_schedule_id FROM audit_schedule_id WHERE customer_ca_id = :id");
            $stSch1->execute([':id' => $clientId]);
            foreach ($stSch1->fetchAll(PDO::FETCH_COLUMN) as $schId) {
                $db->prepare("DELETE FROM audit_schedule WHERE audit_scheduleid = :id")->execute([':id' => $schId]);
            }
            $db->prepare("DELETE FROM audit_schedule_id WHERE customer_ca_id = :id")->execute([':id' => $clientId]);

            // 3. Audit schedule 2
            $stSch2 = $db->prepare("SELECT audit_scheduleii_id FROM audit_scheduleii_id WHERE ca_customer = :id");
            $stSch2->execute([':id' => $clientId]);
            foreach ($stSch2->fetchAll(PDO::FETCH_COLUMN) as $sch2Id) {
                $db->prepare("DELETE FROM audit_scheduleii WHERE scheduleii_id = :id")->execute([':id' => $sch2Id]);
            }
            $db->prepare("DELETE FROM audit_scheduleii_id WHERE ca_customer = :id")->execute([':id' => $clientId]);

            // 4. Contracts
            $stCon = $db->prepare("SELECT contractid FROM contract_id WHERE customer_id = :id");
            $stCon->execute([':id' => $clientId]);
            foreach ($stCon->fetchAll(PDO::FETCH_COLUMN) as $conId) {
                $db->prepare("DELETE FROM contract_review WHERE ca_service_contract = :id")->execute([':id' => $conId]);
            }
            $db->prepare("DELETE FROM contract_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            // 5. Control format 1 & 2
            $stCf1 = $db->prepare("SELECT control_id FROM control_formati_id WHERE customer_id = :id");
            $stCf1->execute([':id' => $clientId]);
            foreach ($stCf1->fetchAll(PDO::FETCH_COLUMN) as $cf1Id) {
                $db->prepare("DELETE FROM control_formati WHERE control_id = :id")->execute([':id' => $cf1Id]);
            }
            $db->prepare("DELETE FROM control_formati_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            $stCf2 = $db->prepare("SELECT control2_id FROM control_formatii_id WHERE customer_id = :id");
            $stCf2->execute([':id' => $clientId]);
            foreach ($stCf2->fetchAll(PDO::FETCH_COLUMN) as $cf2Id) {
                $db->prepare("DELETE FROM control_formatii WHERE control2_id = :id")->execute([':id' => $cf2Id]);
            }
            $db->prepare("DELETE FROM control_formatii_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            // 6. Surveys
            $stSur = $db->prepare("SELECT survey_id FROM customer_survey_id WHERE customer_ca = :id");
            $stSur->execute([':id' => $clientId]);
            foreach ($stSur->fetchAll(PDO::FETCH_COLUMN) as $surId) {
                $db->prepare("DELETE FROM customer_survey WHERE survayid = :id")->execute([':id' => $surId]);
            }
            $db->prepare("DELETE FROM customer_survey_id WHERE customer_ca = :id")->execute([':id' => $clientId]);

            // 7. Surveillance
            $stSvl = $db->prepare("SELECT servelance_id FROM servelance_1_id WHERE customer_id = :id");
            $stSvl->execute([':id' => $clientId]);
            foreach ($stSvl->fetchAll(PDO::FETCH_COLUMN) as $svlId) {
                $db->prepare("DELETE FROM servelance_audit WHERE servelance_id = :id")->execute([':id' => $svlId]);
            }
            $db->prepare("DELETE FROM servelance_1_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            // 8. Stage audits
            $stSt1 = $db->prepare("SELECT stage_i_id FROM stage_i_audit_id WHERE customer_id = :id");
            $stSt1->execute([':id' => $clientId]);
            foreach ($stSt1->fetchAll(PDO::FETCH_COLUMN) as $st1Id) {
                $db->prepare("DELETE FROM stage_i_audit WHERE ca_service_audit = :id")->execute([':id' => $st1Id]);
            }
            $db->prepare("DELETE FROM stage_i_audit_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            $stSt2 = $db->prepare("SELECT stage_ii_id FROM stage_ii_audit_id WHERE customer_id = :id");
            $stSt2->execute([':id' => $clientId]);
            foreach ($stSt2->fetchAll(PDO::FETCH_COLUMN) as $st2Id) {
                $db->prepare("DELETE FROM stage_ii_audit WHERE ca_service_audit_ii = :id")->execute([':id' => $st2Id]);
            }
            $db->prepare("DELETE FROM stage_ii_audit_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            $stO1 = $db->prepare("SELECT audit_stage_id FROM stage_oneaudit_id WHERE ca_customer_id = :id");
            $stO1->execute([':id' => $clientId]);
            foreach ($stO1->fetchAll(PDO::FETCH_COLUMN) as $o1Id) {
                $db->prepare("DELETE FROM stage_audit_one WHERE audit_stage_id = :id")->execute([':id' => $o1Id]);
            }
            $db->prepare("DELETE FROM stage_oneaudit_id WHERE ca_customer_id = :id")->execute([':id' => $clientId]);

            $stO2 = $db->prepare("SELECT audit_stageii_id FROM stage_twoaudit_id WHERE customer_id = :id");
            $stO2->execute([':id' => $clientId]);
            foreach ($stO2->fetchAll(PDO::FETCH_COLUMN) as $o2Id) {
                $db->prepare("DELETE FROM stage_audit_two WHERE audit_stageii_id = :id")->execute([':id' => $o2Id]);
            }
            $db->prepare("DELETE FROM stage_twoaudit_id WHERE customer_id = :id")->execute([':id' => $clientId]);

            // 9. Simple child tables
            foreach ($tablesSimple as $tbl => $col) {
                $db->prepare("DELETE FROM `$tbl` WHERE `$col` = :id")->execute([':id' => $clientId]);
            }

            // 10. Services and Customer
            $db->prepare("DELETE FROM service WHERE customer_id = :id")->execute([':id' => $clientId]);
            $db->prepare("DELETE FROM customer_info WHERE id = :id")->execute([':id' => $clientId]);

            return true;
        } catch (Exception $e) {
            error_log("Failed to permanently delete client $clientId: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitfEmptyRecycleBin')) {
    function aitfEmptyRecycleBin($db, $type = 'all') {
        if (!$db) return false;
        aitfEnsureRecycleSchema($db);

        if ($type === 'all' || $type === 'clients') {
            $stmt = $db->query("SELECT id FROM customer_info WHERE is_deleted = 'YES'");
            $clients = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($clients as $cid) {
                aitfPermanentlyDeleteClient($db, $cid);
            }
        }

        if ($type === 'all' || $type === 'services') {
            $stmt = $db->query("SELECT service_id FROM service WHERE is_deleted = 'YES'");
            $services = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($services as $sid) {
                aitfPermanentlyDeleteService($db, $sid);
            }
        }

        return true;
    }
}

if (!function_exists('aitfGetDeletedServices')) {
    function aitfGetDeletedServices($db) {
        if (!$db) return [];
        aitfEnsureRecycleSchema($db);

        $sql = "SELECT s.service_id, s.service_type, s.customer_id, s.service_status, s.audit_date, s.deleted_date,
                       ci.customer_name, ci.unique_id
                FROM service s
                LEFT JOIN customer_info ci ON s.customer_id = ci.id
                WHERE s.is_deleted = 'YES'
                ORDER BY s.deleted_date DESC, s.service_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('aitfGetDeletedClients')) {
    function aitfGetDeletedClients($db) {
        if (!$db) return [];
        $sql = "SELECT id, customer_name, unique_id, deleted_date FROM customer_info WHERE is_deleted = 'YES' ORDER BY deleted_date DESC, id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

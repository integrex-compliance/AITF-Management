<?php
/**
 * AITF Customer Agreement Auto-Sync Helper
 *
 * Automatically keeps all customer reference numbers and audit records synchronized
 * with the authoritative agreement number (from certification_aggrement / customer_info)
 * without requiring manual DB queries.
 */

if (!function_exists('aitf_sync_customer_agreement_numbers')) {
    function aitf_sync_customer_agreement_numbers($db, $customerId = null) {
        if (!$db) {
            return null;
        }

        try {
            if (!empty($customerId)) {
                $cid = (int)$customerId;
                if ($cid <= 0) {
                    return null;
                }

                // 1. Fetch latest authoritative non-RC agreement number from certification_aggrement
                $agStmt = $db->prepare("SELECT aggrement_no FROM certification_aggrement 
                                       WHERE customer_id = :cid 
                                         AND aggrement_no IS NOT NULL 
                                         AND aggrement_no != '' 
                                         AND aggrement_no NOT LIKE '% Rc-%' 
                                         AND aggrement_no NOT LIKE '%-RC-%' 
                                       ORDER BY id DESC LIMIT 1");
                $agStmt->execute([':cid' => $cid]);
                $agRow = $agStmt->fetch(PDO::FETCH_ASSOC);

                $authAgNo = null;
                if ($agRow && !empty($agRow['aggrement_no'])) {
                    $authAgNo = trim($agRow['aggrement_no']);
                }

                // Fallback to customer_info.unique_id if no certification_aggrement entry exists yet
                if (empty($authAgNo)) {
                    $cStmt = $db->prepare("SELECT unique_id FROM customer_info WHERE id = :cid");
                    $cStmt->execute([':cid' => $cid]);
                    $cRow = $cStmt->fetch(PDO::FETCH_ASSOC);
                    if ($cRow && !empty($cRow['unique_id']) && strpos($cRow['unique_id'], 'Rc-') === false && strpos($cRow['unique_id'], '-RC-') === false) {
                        $authAgNo = trim($cRow['unique_id']);
                    }
                }

                if (empty($authAgNo)) {
                    return null;
                }

                // Ensure customer_info.unique_id matches
                $db->prepare("UPDATE customer_info SET unique_id = :ag WHERE id = :cid AND (unique_id != :ag OR unique_id IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                // Sync allotment_count sequence number if present (e.g. AITF/Sep-26/5 -> 5)
                if (preg_match('/\/(\d+)$/', $authAgNo, $m)) {
                    $seq = (int)$m[1];
                    $db->prepare("UPDATE allotment_count SET client_count = :cnt WHERE client_id = :cid AND client_count != :cnt")
                       ->execute([':cnt' => $seq, ':cid' => $cid]);
                }

                // Sync all audit tables for this customer
                $db->prepare("UPDATE stage_oneaudit_id SET audit_ref_no = :ag WHERE ca_customer_id = :cid AND (audit_ref_no != :ag OR audit_ref_no IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE audit_schedule_id SET ref_no = :ag WHERE customer_ca_id = :cid AND (ref_no != :ag OR ref_no IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE stage_twoaudit_id SET audit_ref_no = :ag WHERE customer_id = :cid AND (audit_ref_no != :ag OR audit_ref_no IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE stage_i_audit_id SET referenceNumber = :ag WHERE customer_id = :cid AND (referenceNumber != :ag OR referenceNumber IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE stage_ii_audit_id SET reference_number = :ag WHERE customer_id = :cid AND (reference_number != :ag OR reference_number IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE control_formati_id SET control_ref_no = :ag WHERE customer_id = :cid AND (control_ref_no != :ag OR control_ref_no IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                $db->prepare("UPDATE control_formatii_id SET control2_ref_no = :ag WHERE customer_id = :cid AND (control2_ref_no != :ag OR control2_ref_no IS NULL)")
                   ->execute([':ag' => $authAgNo, ':cid' => $cid]);

                return $authAgNo;
            } else {
                // Global batch sync across all customers
                $db->exec("UPDATE customer_info ci
                           INNER JOIN (
                               SELECT ca1.customer_id, ca1.aggrement_no
                               FROM certification_aggrement ca1
                               INNER JOIN (
                                   SELECT customer_id, MAX(id) as max_id
                                   FROM certification_aggrement
                                   WHERE aggrement_no IS NOT NULL 
                                     AND aggrement_no != '' 
                                     AND aggrement_no NOT LIKE '% Rc-%' 
                                     AND aggrement_no NOT LIKE '%-RC-%'
                                   GROUP BY customer_id
                               ) latest ON ca1.id = latest.max_id
                           ) auth ON ci.id = auth.customer_id
                           SET ci.unique_id = auth.aggrement_no
                           WHERE ci.unique_id != auth.aggrement_no OR ci.unique_id IS NULL");

                $db->exec("UPDATE allotment_count ac
                           INNER JOIN customer_info ci ON ac.client_id = ci.id
                           SET ac.client_count = CAST(SUBSTRING_INDEX(ci.unique_id, '/', -1) AS UNSIGNED)
                           WHERE ci.unique_id LIKE 'AITF/%'
                             AND ci.unique_id NOT LIKE '% Rc-%'
                             AND ci.unique_id NOT LIKE '%-RC-%'
                             AND ac.client_count != CAST(SUBSTRING_INDEX(ci.unique_id, '/', -1) AS UNSIGNED)");

                $db->exec("UPDATE stage_oneaudit_id soa INNER JOIN customer_info ci ON soa.ca_customer_id = ci.id SET soa.audit_ref_no = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND soa.audit_ref_no != ci.unique_id");
                $db->exec("UPDATE audit_schedule_id asi INNER JOIN customer_info ci ON asi.customer_ca_id = ci.id SET asi.ref_no = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND asi.ref_no != ci.unique_id");
                $db->exec("UPDATE stage_twoaudit_id sta INNER JOIN customer_info ci ON sta.customer_id = ci.id SET sta.audit_ref_no = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND sta.audit_ref_no != ci.unique_id");
                $db->exec("UPDATE stage_i_audit_id sia INNER JOIN customer_info ci ON sia.customer_id = ci.id SET sia.referenceNumber = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND sia.referenceNumber != ci.unique_id");
                $db->exec("UPDATE stage_ii_audit_id siia INNER JOIN customer_info ci ON siia.customer_id = ci.id SET siia.reference_number = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND siia.reference_number != ci.unique_id");
                $db->exec("UPDATE control_formati_id cfi INNER JOIN customer_info ci ON cfi.customer_id = ci.id SET cfi.control_ref_no = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND cfi.control_ref_no != ci.unique_id");
                $db->exec("UPDATE control_formatii_id cfii INNER JOIN customer_info ci ON cfii.customer_id = ci.id SET cfii.control2_ref_no = ci.unique_id WHERE ci.unique_id IS NOT NULL AND ci.unique_id != '' AND ci.unique_id NOT LIKE '% Rc-%' AND ci.unique_id NOT LIKE '%-RC-%' AND cfii.control2_ref_no != ci.unique_id");

                return true;
            }
        } catch (Throwable $e) {
            error_log("aitf_sync_customer_agreement_numbers error: " . $e->getMessage());
            return null;
        }
    }
}

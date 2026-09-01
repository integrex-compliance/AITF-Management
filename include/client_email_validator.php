<?php
/**
 * Helper to verify whether an email address belongs to a registered client
 * across customer_info, online_audit_applications, audit records, and previous submissions.
 */

if (!function_exists('aitf_check_client_email_exists')) {
    function aitf_check_client_email_exists($db, $inputEmail) {
        $cleanEmail = strtolower(trim((string)$inputEmail));
        if (empty($cleanEmail) || !filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'found' => false,
                'message' => 'Please enter a valid email address.'
            ];
        }

        if (!$db) {
            return [
                'found' => false,
                'message' => 'Database connection unavailable.'
            ];
        }

        // 1. Check customer_info (Main CRM client records)
        // Handles single emails and delimited lists (commas, semicolons, slashes, trailing spaces)
        try {
            $stmt = $db->prepare("SELECT id, customer_name, email_id, certification_no, contact_person FROM customer_info WHERE LOWER(email_id) LIKE :term");
            $stmt->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['email_id']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['customer_name']),
                            'cert_no' => trim((string)($row['certification_no'] ?? '')),
                            'contact_person' => trim((string)($row['contact_person'] ?? '')),
                            'source' => 'customer_info'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_check_client_email_exists customer_info error: " . $e->getMessage());
        }

        // 2. Check online_audit_applications (New / online application forms)
        try {
            $stmt2 = $db->prepare("SELECT id, org_name, email, contact_person FROM online_audit_applications WHERE LOWER(email) LIKE :term");
            $stmt2->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['email']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['org_name']),
                            'cert_no' => '',
                            'contact_person' => trim((string)($row['contact_person'] ?? '')),
                            'source' => 'online_audit_applications'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_check_client_email_exists online_audit_applications error: " . $e->getMessage());
        }

        // 3. Check stage_oneaudit_id & stage_twoaudit_id (Audit reports)
        try {
            $stmt3 = $db->prepare("SELECT for_org, audit_email, audit_contact_person FROM stage_oneaudit_id WHERE LOWER(audit_email) LIKE :term LIMIT 20");
            $stmt3->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['audit_email']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['for_org']),
                            'cert_no' => '',
                            'contact_person' => trim((string)($row['audit_contact_person'] ?? '')),
                            'source' => 'stage_oneaudit_id'
                        ];
                    }
                }
            }

            $stmt4 = $db->prepare("SELECT for_org, audit_email, audit_contact_person FROM stage_twoaudit_id WHERE LOWER(audit_email) LIKE :term LIMIT 20");
            $stmt4->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt4->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['audit_email']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['for_org']),
                            'cert_no' => '',
                            'contact_person' => trim((string)($row['audit_contact_person'] ?? '')),
                            'source' => 'stage_twoaudit_id'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_check_client_email_exists audit_stage error: " . $e->getMessage());
        }

        // 4. Check previously submitted information_updation_forms & nc_closing_forms
        try {
            $stmt5 = $db->prepare("SELECT org_name, email, contact_person, certificate_number FROM information_updation_forms WHERE LOWER(email) LIKE :term LIMIT 10");
            $stmt5->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['email']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['org_name']),
                            'cert_no' => trim((string)($row['certificate_number'] ?? '')),
                            'contact_person' => trim((string)($row['contact_person'] ?? '')),
                            'source' => 'information_updation_forms'
                        ];
                    }
                }
            }

            $stmt6 = $db->prepare("SELECT org_name, email FROM nc_closing_forms WHERE LOWER(email) LIKE :term LIMIT 10");
            $stmt6->execute([':term' => '%' . $cleanEmail . '%']);
            while ($row = $stmt6->fetch(PDO::FETCH_ASSOC)) {
                $emails = preg_split('/[\s,;\/]+/', strtolower((string)$row['email']));
                foreach ($emails as $em) {
                    if (trim($em) === $cleanEmail) {
                        return [
                            'found' => true,
                            'org_name' => trim((string)$row['org_name']),
                            'cert_no' => '',
                            'contact_person' => '',
                            'source' => 'nc_closing_forms'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("aitf_check_client_email_exists form_history error: " . $e->getMessage());
        }

        return [
            'found' => false,
            'message' => 'This mail is not valid. Kindly mention the mail ID that is present in your application form.'
        ];
    }
}

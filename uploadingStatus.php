<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Document Upload";
    include 'header.php';
    include 'dbConnect.php';
    require_once __DIR__ . '/include/pending_doc_helper.php';

    if (!aitf_has_access("Doc Upload Status")) {
        echo "<script>window.location.href='homePage.php';</script>";
        exit;
    }

    //    Service CR
    $crAuditApplication = "SELECT cr.cr_id, service, customer_id, application_form, ci.customer_name FROM service_type_cr cr LEFT JOIN customer_info ci on cr.customer_id = ci.id WHERE application_form like '%scanning%' and is_deleted = 'NO' and ci.audit_status <> 'Application canceled' ORDER BY ci.customer_name";
    $statement = $db->prepare($crAuditApplication);
    $statement->execute();
    $crAuditApplicationPending = $statement->fetchAll();

    $crCertificationAgreement = "SELECT cr.cr_id, service, customer_id, certification_aggrement, ci.customer_name FROM service_type_cr cr LEFT JOIN customer_info ci on cr.customer_id = ci.id WHERE certification_aggrement like '%scanning%' and is_deleted = 'NO' and ci.audit_status <> 'Application canceled' ORDER BY ci.customer_name";
    $statement = $db->prepare($crCertificationAgreement);
    $statement->execute();
    $crCertificationAgreementPending = $statement->fetchAll();

    //    Service CA
    $caSignedNotification1 = "SELECT sca.ca_id, service, customer_id, signed_notification, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_notification like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caSignedNotification1);
    $statement->execute();
    $caSignedNotification1Pending = $statement->fetchAll();

    $caStage1Schedule = "SELECT sca.ca_id, service, customer_id, signed_copy, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_copy like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage1Schedule);
    $statement->execute();
    $caStage1SchedulePending = $statement->fetchAll();

    $caStage1AuditReport = "SELECT sca.ca_id, service, customer_id, audit_report_I, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_report_I like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage1AuditReport);
    $statement->execute();
    $caStage1AuditReportPending = $statement->fetchAll();

    $caStage2SgnedCopy = "SELECT sca.ca_id, service, customer_id, signed_notification_II, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_notification_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage2SgnedCopy);
    $statement->execute();
    $caStage2SgnedCopyPending = $statement->fetchAll();

    $caAuditReport2 = "SELECT sca.ca_id, service, customer_id, audit_report_II, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_report_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditReport2);
    $statement->execute();
    $caAuditReport2Pending = $statement->fetchAll();

    $caAuditorNotes = "SELECT sca.ca_id, service, customer_id, auditor_notes, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE auditor_notes like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditorNotes);
    $statement->execute();
    $caAuditorNotesPending = $statement->fetchAll();

    $caAuditNC = "SELECT sca.ca_id, service, customer_id, audit_nc_II, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_nc_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditNC);
    $statement->execute();
    $caAuditNCPending = $statement->fetchAll();

    $caCertificate = "SELECT sca.ca_id, service, customer_id, certificate_print, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caCertificate);
    $statement->execute();
    $caCertificatePending = $statement->fetchAll();

    $caCustomerFeedback = "SELECT sca.ca_id, service, customer_id, customer_satisfaction, ci.customer_name, sca.stage1_auditor, sca.stage2_auditor FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE customer_satisfaction like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caCustomerFeedback);
    $statement->execute();
    $caCustomerFeedbackPending = $statement->fetchAll();

    //    Service SA-I
    
    $saiInformationUpdatoin = "SELECT sai.sai_id, service, customer_id, info_update_form, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE info_update_form like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiInformationUpdatoin);
    $statement->execute();
    $saiInformationUpdatoinPending = $statement->fetchAll();

    $saiSignedNotification = "SELECT sai.sai_id, service, customer_id, signed_noti_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE signed_noti_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiSignedNotification);
    $statement->execute();
    $saiSignedNotificationPending = $statement->fetchAll();

    $saiSignedCopy = "SELECT sai.sai_id, service, customer_id, signed_copy_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE signed_copy_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiSignedCopy);
    $statement->execute();
    $saiSignedCopyPending = $statement->fetchAll();

    $saiAuditReport = "SELECT sai.sai_id, service, customer_id, audit_report_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE audit_report_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditReport);
    $statement->execute();
    $saiAuditReportPending = $statement->fetchAll();

    $saiAuditorNotes = "SELECT sai.sai_id, service, customer_id, auditor_notes_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE auditor_notes_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditorNotes);
    $statement->execute();
    $saiAuditorNotesPending = $statement->fetchAll();

    $saiAuditEvidence = "SELECT sai.sai_id, service, customer_id, audit_evidance_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE audit_evidance_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditEvidence);
    $statement->execute();
    $saiAuditEvidencePending = $statement->fetchAll();

    $saiCertificate = "SELECT sai.sai_id, service, customer_id, certificate_print, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiCertificate);
    $statement->execute();
    $saiCertificatePending = $statement->fetchAll();

    $saiCustomerReview = "SELECT sai.sai_id, service, customer_id, customer_review_sai, ci.customer_name, sai.auditors FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE customer_review_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiCustomerReview);
    $statement->execute();
    $saiCustomerReviewPending = $statement->fetchAll();

    //    service type SA-II
    
    $saiiInformationUpdatoin = "SELECT saii.saii_id, service, customer_id, update_info_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE update_info_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiInformationUpdatoin);
    $statement->execute();
    $saiiInformationUpdatoinPending = $statement->fetchAll();

    $saiiSignedNotification = "SELECT saii.saii_id, service, customer_id, signed_noti_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE signed_noti_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiSignedNotification);
    $statement->execute();
    $saiiSignedNotificationPending = $statement->fetchAll();

    $saiiSignedCopy = "SELECT saii.saii_id, service, customer_id, signed_copy_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE signed_copy_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiSignedCopy);
    $statement->execute();
    $saiiSignedCopyPending = $statement->fetchAll();

    $saiiAuditReport = "SELECT saii.saii_id, service, customer_id, audit_report_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE audit_report_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditReport);
    $statement->execute();
    $saiiAuditReportPending = $statement->fetchAll();

    $saiiAuditorNotes = "SELECT saii.saii_id, service, customer_id, auditor_notes_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE auditor_notes_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditorNotes);
    $statement->execute();
    $saiiAuditorNotesPending = $statement->fetchAll();

    $saiiAuditorEvidence = "SELECT saii.saii_id, service, customer_id, audit_evidences_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE audit_evidences_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditorEvidence);
    $statement->execute();
    $saiiAuditorEvidencePending = $statement->fetchAll();

    $saiiCertificate = "SELECT saii.saii_id, service, customer_id, certificate_print, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiCertificate);
    $statement->execute();
    $saiiCertificatePending = $statement->fetchAll();

    $saiiFeedback = "SELECT saii.saii_id, service, customer_id, customer_review_saii, ci.customer_name, saii.auditors FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE customer_review_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiFeedback);
    $statement->execute();
    $saiiFeedbackPending = $statement->fetchAll();

    //    RC service
    
    $rcNotification = "SELECT rc.rc_id, service, rc_customer, rc_signed_noti, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_signed_noti like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcNotification);
    $statement->execute();
    $rcNotificationPending = $statement->fetchAll();

    $rcSignedSchedule = "SELECT rc.rc_id, service, rc_customer, rc_signed_copy, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_signed_copy like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcSignedSchedule);
    $statement->execute();
    $rcSignedSchedulePending = $statement->fetchAll();

    $rcAuditReport = "SELECT rc.rc_id, service, rc_customer, rc_audit_report, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_audit_report like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAuditReport);
    $statement->execute();
    $rcAuditReportPending = $statement->fetchAll();

    $rcAuditorNotes = "SELECT rc.rc_id, service, rc_customer, auditor_notes_rc, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE auditor_notes_rc like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAuditorNotes);
    $statement->execute();
    $rcAuditorNotesPending = $statement->fetchAll();

    $rcAudiEvidence = "SELECT rc.rc_id, service, rc_customer, rc_audit_evidence, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_audit_evidence like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAudiEvidence);
    $statement->execute();
    $rcAudiEvidencePending = $statement->fetchAll();

    $rcCertificate = "SELECT rc.rc_id, service, rc_customer, certificate_print, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcCertificate);
    $statement->execute();
    $rcCertificatePending = $statement->fetchAll();

    $rcCustomerReview = "SELECT rc.rc_id, service, rc_customer, rc_customer_review, ci.customer_name, rc.auditors FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_customer_review like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcCustomerReview);
    $statement->execute();
    $rcCustomerReviewPending = $statement->fetchAll();

    $totalPendingDocuments = count($crAuditApplicationPending) + count($crCertificationAgreementPending) +
        count($caSignedNotification1Pending) + count($caStage1SchedulePending) + count($caStage1AuditReportPending) +
        count($caStage2SgnedCopyPending) + count($caAuditReport2Pending) + count($caAuditorNotesPending) +
        count($caAuditNCPending) + count($caCertificatePending) + count($caCustomerFeedbackPending) +
        count($saiInformationUpdatoinPending) + count($saiSignedNotificationPending) + count($saiSignedCopyPending) +
        count($saiAuditReportPending) + count($saiAuditorNotesPending) + count($saiAuditEvidencePending) +
        count($saiCertificatePending) + count($saiCustomerReviewPending) + count($saiiInformationUpdatoinPending) +
        count($saiiSignedNotificationPending) + count($saiiSignedCopyPending) + count($saiiAuditReportPending) +
        count($saiiAuditorNotesPending) + count($saiiAuditorEvidencePending) + count($saiiCertificatePending) +
        count($saiiFeedbackPending) + count($rcNotificationPending) + count($rcSignedSchedulePending) +
        count($rcAuditReportPending) + count($rcAuditorNotesPending) + count($rcAudiEvidencePending) +
        count($rcCertificatePending) + count($rcCustomerReviewPending);

    // Fetch communications from auditors
    $commMap = aitf_get_pending_doc_communications_map($db);
    $allCommunications = aitf_get_pending_doc_communications_list($db, 300);

    if (!function_exists('uploadingSectionTitle')) {
        function uploadingSectionTitle($label, $items)
        {
            return $label . ' <span class="uploading-section-count">(' . count($items) . ')</span>';
        }
    }

    if (!function_exists('renderPendingDocCard')) {
        function renderPendingDocCard($customerName, $serviceType, $serviceId, $docKey, $assignedAuditor, $commMap)
        {
            $key = strtoupper(trim($serviceType)) . '_' . $serviceId . '_' . trim($docKey);
            $comm = $commMap[$key] ?? null;
            $status = $comm['status'] ?? '';
            $commAuditor = $comm['auditor_name'] ?? '';
            $note = $comm['note'] ?? '';
            $updatedAt = !empty($comm['updated_at']) ? date('d M Y, h:i A', strtotime($comm['updated_at'])) : '';

            $cleanAssigned = aitf_clean_auditor_names($assignedAuditor);
            ?>
            <div class="serviceContentwaiting">
                <span class="doc-client-title"><?php echo htmlspecialchars($customerName); ?></span>
                <?php if ($status === 'YES') { ?>
                    <div class="doc-status-pill sent" title="<?php echo htmlspecialchars($note ? 'Note: ' . $note . ($updatedAt ? ' (' . $updatedAt . ')' : '') : 'Marked as Sent'); ?>">
                        <i class="fa fa-check-circle"></i> Send by <?php echo htmlspecialchars($commAuditor ?: $cleanAssigned); ?>
                        <?php if ($note !== '') { ?>
                            <span class="note-tip" title="<?php echo htmlspecialchars($note); ?>"><i class="fa fa-commenting-o"></i></span>
                        <?php } ?>
                    </div>
                <?php } elseif ($status === 'NO') { ?>
                    <div class="doc-status-pill pending-reason" title="<?php echo htmlspecialchars($note ? 'Reason: ' . $note . ($updatedAt ? ' (' . $updatedAt . ')' : '') : 'Marked as Pending'); ?>">
                        <i class="fa fa-clock-o"></i> Pending from <?php echo htmlspecialchars($commAuditor ?: $cleanAssigned); ?>
                        <?php if ($note !== '') { ?>
                            <span class="note-tip" title="<?php echo htmlspecialchars('Reason: ' . $note); ?>"><i class="fa fa-info-circle"></i></span>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="doc-status-pill neutral">
                        <i class="fa fa-user-o"></i> Auditor: <?php echo htmlspecialchars($cleanAssigned ?: 'Pending Assignment'); ?>
                    </div>
                <?php } ?>
            </div>
            <?php
        }
    }
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container">
                <style>
                    /* Clean typography & modern color scheme for Uploading Status */
                    .container {
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    }

                    /* Summary Header Card */
                    .row:first-of-type .panel.panel-primary {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                        padding: 18px 20px;
                        margin-bottom: 20px;
                        text-align: center;
                    }

                    .row:first-of-type .panel.panel-primary h4 {
                        color: #0d4d8b !important;
                        font-size: 20px !important;
                        font-weight: 800 !important;
                        letter-spacing: -0.3px;
                        margin: 0 0 8px 0;
                    }

                    .row:first-of-type .panel.panel-primary p {
                        font-size: 13px;
                        font-weight: 700;
                        margin-bottom: 0;
                        display: inline-block;
                        background: #fef2f2;
                        color: #dc2626 !important;
                        padding: 4px 16px;
                        border-radius: 20px;
                        border: 1px solid #fecaca;
                    }

                    /* Accordion Service Panels */
                    .panel.panel-primary {
                        border: 1px solid #e2e8f0;
                        border-radius: 10px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
                        margin-bottom: 16px;
                        overflow: hidden;
                        background: #ffffff;
                        transition: box-shadow 0.2s ease;
                    }

                    .panel.panel-primary:hover {
                        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
                    }

                    /* Clickable Section Headers */
                    .panel-heading.CR-Head,
                    .panel-heading.CA-Head,
                    .panel-heading.SAi-Head,
                    .panel-heading.SAii-Head,
                    .panel-heading.RC-Head {
                        background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%) !important;
                        height: auto !important;
                        padding: 12px 18px !important;
                        cursor: pointer;
                        user-select: none;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.2s ease;
                    }

                    .panel-heading.CR-Head:hover,
                    .panel-heading.CA-Head:hover,
                    .panel-heading.SAi-Head:hover,
                    .panel-heading.SAii-Head:hover,
                    .panel-heading.RC-Head:hover {
                        background: linear-gradient(135deg, #11599e 0%, #0a467e 100%) !important;
                    }

                    .panel-heading h4 {
                        font-size: 14.5px !important;
                        font-weight: 700 !important;
                        letter-spacing: 0.3px;
                        color: #ffffff !important;
                        margin: 0 !important;
                    }

                    /* Panel Body */
                    .panel-rightbody.homePagePanel {
                        padding: 16px 18px;
                        background: #f8fafc;
                        border-top: 1px solid #e2e8f0;
                    }

                    /* Column Category Title Buttons */
                    .btn.qms_complete_btn {
                        background: #0d4d8b !important;
                        color: #ffffff !important;
                        border: none !important;
                        border-radius: 6px !important;
                        padding: 6px 10px !important;
                        height: auto !important;
                        min-height: 32px !important;
                        font-size: 12px !important;
                        font-weight: 700 !important;
                        letter-spacing: 0.2px;
                        text-transform: none;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
                        margin-bottom: 8px;
                        cursor: default;
                    }

                    .btn.qms_complete_btn span i.fa {
                        font-style: normal;
                        font-family: inherit;
                        font-weight: 700;
                    }

                    .uploading-section-count {
                        font-style: normal;
                        font-size: 11px;
                        margin-left: 4px;
                        padding: 1px 6px;
                        background: rgba(255, 255, 255, 0.22);
                        border-radius: 10px;
                        color: #bae6fd;
                    }

                    /* Pending Document Items */
                    .serviceContentwaiting {
                        height: auto;
                        min-height: 26px;
                        white-space: normal;
                        overflow-wrap: anywhere;
                        word-break: break-word;
                        line-height: 1.35;
                        padding: 6px 10px;
                        margin-bottom: 5px;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-left: 3px solid #f59e0b;
                        border-radius: 5px;
                        color: #1e293b;
                        font-size: 12px;
                        font-weight: 600;
                        transition: background 0.15s ease, border-color 0.15s ease;
                    }

                    .serviceContentwaiting:hover {
                        background: #fffbeb;
                        border-color: #fde68a;
                        border-left-color: #d97706;
                    }

                    .serviceContentwaiting span {
                        display: block;
                    }

                    /* Completed Document State */
                    .serviceContentCompleted {
                        height: auto;
                        min-height: 26px;
                        white-space: normal;
                        overflow-wrap: anywhere;
                        word-break: break-word;
                        line-height: 1.35;
                        padding: 6px 10px;
                        margin-bottom: 5px;
                        background: #f0fdf4;
                        border: 1px solid #bbf7d0;
                        border-left: 3px solid #10b981;
                        border-radius: 5px;
                        color: #047857;
                        font-size: 11.5px;
                        font-weight: 700;
                    }

                    .serviceContentCompleted span {
                        display: block;
                    }

                    /* Document Status Pills and Communication Styles */
                    .doc-client-title {
                        display: block;
                        font-size: 12.5px;
                        font-weight: 700;
                        color: #1e293b;
                        margin-bottom: 3px;
                    }

                    .doc-status-pill {
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        font-size: 10.5px;
                        font-weight: 700;
                        padding: 3px 8px;
                        border-radius: 12px;
                        margin-top: 3px;
                        line-height: 1.3;
                    }

                    .doc-status-pill.sent {
                        background: #ecfdf5;
                        color: #059669;
                        border: 1px solid #a7f3d0;
                    }

                    .doc-status-pill.pending-reason {
                        background: #fef2f2;
                        color: #dc2626;
                        border: 1px solid #fecaca;
                    }

                    .doc-status-pill.neutral {
                        background: #f1f5f9;
                        color: #475569;
                        border: 1px solid #e2e8f0;
                    }

                    .doc-status-pill .note-tip {
                        margin-left: 4px;
                        color: #0284c7;
                        cursor: help;
                    }

                    /* =========================================================
                       Modern Clean Hero Header & Segmented Tabs Bar
                       ========================================================= */
                    .upload-hero-card {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 14px;
                        padding: 18px 22px 14px;
                        margin-top: 10px;
                        margin-bottom: 20px;
                        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.03);
                    }

                    .hero-card-top {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 14px;
                        padding-bottom: 14px;
                        border-bottom: 1px solid #f1f5f9;
                    }

                    .hero-branding {
                        display: flex;
                        align-items: center;
                        gap: 14px;
                    }

                    .hero-icon-box {
                        width: 44px;
                        height: 44px;
                        border-radius: 12px;
                        background: linear-gradient(135deg, #053c6d 0%, #0284c7 100%);
                        color: #ffffff;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 20px;
                        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
                        flex-shrink: 0;
                    }

                    .hero-icon-box i {
                        display: inline-block !important;
                        margin: 0 !important;
                        line-height: 1 !important;
                    }

                    .hero-text {
                        line-height: 1.25;
                    }

                    .hero-main-heading {
                        margin: 0 0 3px 0;
                        font-size: 19px;
                        font-weight: 800;
                        color: #0f172a;
                        letter-spacing: -0.3px;
                    }

                    .hero-sub-heading {
                        margin: 0;
                        font-size: 13px;
                        color: #64748b;
                        font-weight: 500;
                    }

                    .hero-top-right {
                        display: flex;
                        align-items: center;
                    }

                    .btn-auditor-portal-link {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 8px !important;
                        padding: 8px 18px !important;
                        border-radius: 8px !important;
                        font-size: 13px !important;
                        font-weight: 700 !important;
                        color: #0284c7 !important;
                        background: #f0f9ff !important;
                        border: 1.5px solid #bae6fd !important;
                        text-decoration: none !important;
                        white-space: nowrap !important;
                        line-height: 1.4 !important;
                        transition: all 0.2s ease !important;
                    }

                    .btn-auditor-portal-link:hover {
                        background: #0284c7 !important;
                        color: #ffffff !important;
                        border-color: #0284c7 !important;
                        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25) !important;
                        text-decoration: none !important;
                        transform: translateY(-1px);
                    }

                    .hero-card-bottom {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 12px;
                        padding-top: 14px;
                    }

                    /* Modern Segmented Tab Navigation */
                    .hero-tabs-nav {
                        display: inline-flex !important;
                        flex-direction: row !important;
                        align-items: center !important;
                        background: #f1f5f9 !important;
                        padding: 4px !important;
                        border-radius: 10px !important;
                        margin: 0 !important;
                        border: none !important;
                        list-style: none !important;
                        gap: 4px !important;
                    }

                    .hero-tabs-nav > li {
                        float: none !important;
                        display: inline-block !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        border: none !important;
                    }

                    .hero-tabs-nav > li > a {
                        display: inline-flex !important;
                        flex-direction: row !important;
                        align-items: center !important;
                        gap: 8px !important;
                        padding: 8px 16px !important;
                        border-radius: 8px !important;
                        font-size: 13.5px !important;
                        font-weight: 700 !important;
                        color: #475569 !important;
                        text-decoration: none !important;
                        white-space: nowrap !important;
                        border: 1px solid transparent !important;
                        background: transparent !important;
                        line-height: 1.4 !important;
                        transition: all 0.2s ease !important;
                    }

                    .hero-tabs-nav > li > a i.fa {
                        font-size: 14px !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        display: inline-block !important;
                        line-height: 1 !important;
                        float: none !important;
                        width: auto !important;
                        height: auto !important;
                    }

                    .hero-tabs-nav > li > a span.tab-title {
                        display: inline !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        float: none !important;
                        white-space: nowrap !important;
                    }

                    .hero-tabs-nav > li:hover > a {
                        color: #0f172a !important;
                        background: rgba(255, 255, 255, 0.6) !important;
                    }

                    .hero-tabs-nav > li.active > a,
                    .hero-tabs-nav > li.active > a:hover,
                    .hero-tabs-nav > li.active > a:focus {
                        color: #053c6d !important;
                        background: #ffffff !important;
                        border-color: rgba(226, 232, 240, 0.9) !important;
                        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08) !important;
                    }

                    /* Badges inside tabs */
                    .hero-counter-badge {
                        display: inline-flex !important;
                        align-items: center !important;
                        padding: 2px 9px !important;
                        border-radius: 12px !important;
                        font-size: 11.5px !important;
                        font-weight: 800 !important;
                        line-height: 1.2 !important;
                        letter-spacing: 0.2px !important;
                        white-space: nowrap !important;
                        float: none !important;
                        margin: 0 0 0 4px !important;
                    }

                    .hero-counter-badge.counter-red {
                        background: #fef2f2 !important;
                        color: #dc2626 !important;
                        border: 1px solid #fecaca !important;
                    }

                    .hero-counter-badge.counter-blue {
                        background: #eff6ff !important;
                        color: #1d4ed8 !important;
                        border: 1px solid #bfdbfe !important;
                    }

                    .hero-status-pills {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }

                    .hero-status-tag {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        font-size: 12px;
                        font-weight: 600;
                        color: #64748b;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        padding: 4px 10px;
                        border-radius: 6px;
                        white-space: nowrap;
                    }

                    /* Communication Panel & Table */
                    .comm-panel {
                        border-color: #053c6d;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07);
                    }

                    .comm-panel-heading {
                        background: #053c6d !important;
                        color: #fff !important;
                        padding: 12px 18px;
                    }

                    .comm-header-flex {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                    }

                    .comm-header-stats {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }

                    .comm-stat-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        font-size: 12px;
                        font-weight: 700;
                        padding: 4px 10px;
                        border-radius: 20px;
                        background: rgba(255, 255, 255, 0.2);
                        color: #fff;
                    }

                    /* Explicitly protect all text/spans inside comm-table from external hide rules */
                    .comm-table,
                    .comm-table * {
                        box-sizing: border-box;
                    }

                    .comm-table span,
                    .comm-table td span,
                    .comm-table th span,
                    .comm-table div span {
                        display: inline !important;
                    }

                    .comm-table thead th {
                        background: #f8fafc;
                        color: #334155;
                        font-size: 12px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        border-bottom: 2px solid #cbd5e1 !important;
                        padding: 10px 12px !important;
                    }

                    .comm-table tbody tr:nth-of-type(odd) {
                        background-color: #ffffff;
                    }

                    .comm-table tbody tr:nth-of-type(even) {
                        background-color: #f8fafc;
                    }

                    .comm-table tbody tr:hover {
                        background-color: #f0f9ff !important;
                    }

                    .comm-table tbody td {
                        padding: 12px !important;
                        vertical-align: middle !important;
                    }

                    .comm-service-badge {
                        display: inline-block !important;
                        padding: 3px 8px !important;
                        font-size: 11.5px !important;
                        font-weight: 700 !important;
                        border-radius: 6px !important;
                        color: #ffffff !important;
                        background: #053c6d !important;
                        text-align: center;
                    }

                    .comm-doc-name {
                        font-weight: 700 !important;
                        color: #1e293b !important;
                        font-size: 13px !important;
                        display: block !important;
                    }

                    /* Auditor Status Pill */
                    .comm-status-badge {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        padding: 5px 12px !important;
                        font-size: 12px !important;
                        font-weight: 700 !important;
                        border-radius: 20px !important;
                        white-space: nowrap !important;
                    }

                    .comm-status-badge.sent {
                        background: #dcfce7 !important;
                        color: #15803d !important;
                        border: 1px solid #bbf7d0 !important;
                    }

                    .comm-status-badge.pending {
                        background: #fee2e2 !important;
                        color: #b91c1c !important;
                        border: 1px solid #fecaca !important;
                    }

                    /* Rich Auditor Note Card */
                    .comm-note-card {
                        background: #ffffff;
                        border-radius: 8px;
                        padding: 8px 12px;
                        font-size: 12.5px;
                        line-height: 1.45;
                        border: 1px solid #e2e8f0;
                        border-left: 4px solid #94a3b8;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                    }

                    .comm-note-card.yes-card {
                        background: #f0fdf4 !important;
                        border: 1px solid #bbf7d0 !important;
                        border-left: 4px solid #10b981 !important;
                        color: #14532d !important;
                    }

                    .comm-note-card.no-card {
                        background: #fef2f2 !important;
                        border: 1px solid #fecaca !important;
                        border-left: 4px solid #ef4444 !important;
                        color: #7f1d1d !important;
                    }

                    .comm-note-title {
                        display: flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        font-weight: 700 !important;
                        font-size: 11px !important;
                        margin-bottom: 3px !important;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                    }

                    .comm-note-body {
                        font-size: 12.5px !important;
                        font-weight: 600 !important;
                        word-break: break-word !important;
                        white-space: pre-wrap !important;
                        display: block !important;
                    }

                    /* Dark Theme Support */
                    html.theme-dark .panel.panel-primary {
                        background: #0f172a;
                        border-color: #1e293b;
                    }

                    html.theme-dark .row:first-of-type .panel.panel-primary {
                        background: #0f172a;
                        border-color: #1e293b;
                    }

                    html.theme-dark .row:first-of-type .panel.panel-primary h4 {
                        color: #38bdf8 !important;
                    }

                    html.theme-dark .row:first-of-type .panel.panel-primary p {
                        background: rgba(220, 38, 38, 0.15);
                        color: #fca5a5 !important;
                        border-color: rgba(220, 38, 38, 0.3);
                    }

                    html.theme-dark .panel-rightbody.homePagePanel {
                        background: #0b1120;
                        border-top-color: #1e293b;
                    }

                    html.theme-dark .serviceContentwaiting {
                        background: #1e293b;
                        border-color: #334155;
                        border-left-color: #f59e0b;
                        color: #f1f5f9;
                    }

                    html.theme-dark .serviceContentwaiting:hover {
                        background: #334155;
                    }

                    html.theme-dark .serviceContentCompleted {
                        background: rgba(16, 185, 129, 0.15);
                        border-color: rgba(16, 185, 129, 0.3);
                        border-left-color: #10b981;
                        color: #6ee7b7;
                    }

                    html.theme-dark .doc-client-title {
                        color: #f1f5f9;
                    }

                    html.theme-dark .doc-status-pill.sent {
                        background: rgba(16, 185, 129, 0.2);
                        color: #6ee7b7;
                        border-color: rgba(16, 185, 129, 0.4);
                    }

                    html.theme-dark .doc-status-pill.pending-reason {
                        background: rgba(239, 68, 68, 0.2);
                        color: #fca5a5;
                        border-color: rgba(239, 68, 68, 0.4);
                    }

                    html.theme-dark .doc-status-pill.neutral {
                        background: #334155;
                        color: #cbd5e1;
                        border-color: #475569;
                    }

                    html.theme-dark .upload-hero-card {
                        background: #0f172a;
                        border-color: #1e293b;
                        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35);
                    }

                    html.theme-dark .hero-card-top {
                        border-bottom-color: #1e293b;
                    }

                    html.theme-dark .hero-main-heading {
                        color: #f1f5f9;
                    }

                    html.theme-dark .hero-sub-heading {
                        color: #94a3b8;
                    }

                    html.theme-dark .btn-auditor-portal-link {
                        background: rgba(2, 132, 199, 0.15) !important;
                        border-color: rgba(2, 132, 199, 0.35) !important;
                        color: #38bdf8 !important;
                    }

                    html.theme-dark .btn-auditor-portal-link:hover {
                        background: #0284c7 !important;
                        color: #ffffff !important;
                    }

                    html.theme-dark .hero-tabs-nav {
                        background: #1e293b !important;
                    }

                    html.theme-dark .hero-tabs-nav > li > a {
                        color: #94a3b8 !important;
                    }

                    html.theme-dark .hero-tabs-nav > li:hover > a {
                        color: #f1f5f9 !important;
                        background: rgba(255, 255, 255, 0.05) !important;
                    }

                    html.theme-dark .hero-tabs-nav > li.active > a,
                    html.theme-dark .hero-tabs-nav > li.active > a:hover,
                    html.theme-dark .hero-tabs-nav > li.active > a:focus {
                        color: #38bdf8 !important;
                        background: #0f172a !important;
                        border-color: #334155 !important;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
                    }

                    html.theme-dark .hero-status-tag {
                        background: #1e293b;
                        border-color: #334155;
                        color: #94a3b8;
                    }

                    html.theme-dark .comm-table thead th {
                        background: #1e293b;
                        color: #cbd5e1;
                        border-color: #334155 !important;
                    }

                    html.theme-dark .comm-table tbody tr:nth-of-type(odd) {
                        background: #0f172a;
                        color: #f1f5f9;
                    }

                    html.theme-dark .comm-table tbody tr:nth-of-type(even) {
                        background: #0b1120;
                        color: #f1f5f9;
                    }

                    html.theme-dark .comm-table tbody tr:hover {
                        background: #1e293b !important;
                    }

                    html.theme-dark .comm-doc-name {
                        color: #f1f5f9 !important;
                    }

                    html.theme-dark .comm-note-card.yes-card {
                        background: rgba(16, 185, 129, 0.15) !important;
                        border: 1px solid rgba(16, 185, 129, 0.35) !important;
                        border-left: 4px solid #10b981 !important;
                        color: #6ee7b7 !important;
                    }

                    html.theme-dark .comm-note-card.no-card {
                        background: rgba(239, 68, 68, 0.15) !important;
                        border: 1px solid rgba(239, 68, 68, 0.35) !important;
                        border-left: 4px solid #ef4444 !important;
                        color: #fca5a5 !important;
                    }
                </style>

                <!-- Modern Clean Hero Header & Segmented Tabs Bar -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="upload-hero-card">
                            <div class="hero-card-top">
                                <div class="hero-branding">
                                    <div class="hero-icon-box">
                                        <i class="fa fa-cloud-upload"></i>
                                    </div>
                                    <div class="hero-text">
                                        <h2 class="hero-main-heading">Document Uploading & Auditor Status</h2>
                                        <p class="hero-sub-heading">Live tracking of pending scanned audit documents across all services and auditor remarks</p>
                                    </div>
                                </div>
                                <div class="hero-top-right">
                                    <a href="auditorPendingDocuments.php" target="_blank" class="btn-auditor-portal-link">
                                        <i class="fa fa-external-link"></i>
                                        <span>Auditor Pending Portal</span>
                                    </a>
                                </div>
                            </div>

                            <div class="hero-card-bottom">
                                <ul class="hero-tabs-nav" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#tabUploadingStatus" aria-controls="tabUploadingStatus" role="tab" data-toggle="tab">
                                            <i class="fa fa-tasks"></i>
                                            <span class="tab-title">Uploading Status</span>
                                            <span class="hero-counter-badge counter-red"><?php echo $totalPendingDocuments; ?> Pending</span>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#tabCommunication" aria-controls="tabCommunication" role="tab" data-toggle="tab">
                                            <i class="fa fa-comments"></i>
                                            <span class="tab-title">Auditor Communication</span>
                                            <span class="hero-counter-badge counter-blue"><?php echo count($allCommunications); ?> Notes</span>
                                        </a>
                                    </li>
                                </ul>

                                <div class="hero-status-pills hidden-xs">
                                    <span class="hero-status-tag"><i class="fa fa-shield text-info"></i> 5 Services Monitored</span>
                                    <span class="hero-status-tag"><i class="fa fa-bolt text-warning"></i> Realtime Sync</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <!-- Tab 1: Uploading Status (Existing Accordions) -->
                    <div role="tabpanel" class="tab-pane active" id="tabUploadingStatus">

                <!--CA-Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px">
                            <div class="panel-heading text-center CR-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">Contract Review</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel CR-body" id="actionItemDisplayPanel"
                                style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                             <div class="col-xs-6 text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Application Form', $crAuditApplicationPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-6  text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Certification Agreement', $crCertificationAgreementPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?php
                                                if ($crAuditApplicationPending) {
                                                    foreach ($crAuditApplicationPending as $applicationFormPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $applicationFormPending["customer_name"] ?></span>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <?php
                                                if ($crCertificationAgreementPending) {
                                                    foreach ($crCertificationAgreementPending as $crCertificationPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $crCertificationPending["customer_name"] ?></span>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--CA-Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px">
                            <div class="panel-heading text-center CA-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - CA</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel CA-body" id="actionItemDisplayPanel"
                                style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-1 Notification', $caSignedNotification1Pending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-1 Schedule', $caStage1SchedulePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-1 Audit Report', $caStage1AuditReportPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caSignedNotification1Pending) {
                                                    foreach ($caSignedNotification1Pending as $notificationPending) {
                                                        renderPendingDocCard($notificationPending["customer_name"], 'CA', $notificationPending['ca_id'], 'signed_notification', $notificationPending['stage1_auditor'] ?: $notificationPending['stage2_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caStage1SchedulePending) {
                                                    foreach ($caStage1SchedulePending as $schedulePending) {
                                                        renderPendingDocCard($schedulePending["customer_name"], 'CA', $schedulePending['ca_id'], 'signed_copy', $schedulePending['stage1_auditor'] ?: $schedulePending['stage2_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caStage1AuditReportPending) {
                                                    foreach ($caStage1AuditReportPending as $auditReport1Pending) {
                                                        renderPendingDocCard($auditReport1Pending["customer_name"], 'CA', $auditReport1Pending['ca_id'], 'audit_report_I', $auditReport1Pending['stage1_auditor'] ?: $auditReport1Pending['stage2_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-2 Notification', $caStage2SgnedCopyPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-2 Audit Report', $caAuditReport2Pending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Stage-2 Auditor Notes', $caAuditorNotesPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caStage2SgnedCopyPending) {
                                                    foreach ($caStage2SgnedCopyPending as $stage2signCopyPending) {
                                                        renderPendingDocCard($stage2signCopyPending["customer_name"], 'CA', $stage2signCopyPending['ca_id'], 'signed_notification_II', $stage2signCopyPending['stage2_auditor'] ?: $stage2signCopyPending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caAuditReport2Pending) {
                                                    foreach ($caAuditReport2Pending as $auditReport2Pending) {
                                                        renderPendingDocCard($auditReport2Pending["customer_name"], 'CA', $auditReport2Pending['ca_id'], 'audit_report_II', $auditReport2Pending['stage2_auditor'] ?: $auditReport2Pending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caAuditorNotesPending) {
                                                    foreach ($caAuditorNotesPending as $auditNotesPending) {
                                                        renderPendingDocCard($auditNotesPending["customer_name"], 'CA', $auditNotesPending['ca_id'], 'auditor_notes', $auditNotesPending['stage2_auditor'] ?: $auditNotesPending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('NC and Evidances', $caAuditNCPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Certificate', $caCertificatePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Client Feedback', $caCustomerFeedbackPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caAuditNCPending) {
                                                    foreach ($caAuditNCPending as $auditNCPending) {
                                                        renderPendingDocCard($auditNCPending["customer_name"], 'CA', $auditNCPending['ca_id'], 'audit_nc_II', $auditNCPending['stage2_auditor'] ?: $auditNCPending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caCertificatePending) {
                                                    foreach ($caCertificatePending as $certificatePending) {
                                                        renderPendingDocCard($certificatePending["customer_name"], 'CA', $certificatePending['ca_id'], 'certificate_print', $certificatePending['stage2_auditor'] ?: $certificatePending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caCustomerFeedbackPending) {
                                                    foreach ($caCustomerFeedbackPending as $customerFeedbackPending) {
                                                        renderPendingDocCard($customerFeedbackPending["customer_name"], 'CA', $customerFeedbackPending['ca_id'], 'customer_satisfaction', $customerFeedbackPending['stage2_auditor'] ?: $customerFeedbackPending['stage1_auditor'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--SA-I Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px">
                            <div class="panel-heading text-center SAi-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - SA-I</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel SAi-body" id="actionItemDisplayPanel"
                                style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Information Updation', $saiInformationUpdatoinPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Signed Notification', $saiSignedNotificationPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Schedule Signed Copy', $saiSignedCopyPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiInformationUpdatoinPending) {
                                                    foreach ($saiInformationUpdatoinPending as $infoUpdationPending) {
                                                        renderPendingDocCard($infoUpdationPending["customer_name"], 'SA-I', $infoUpdationPending['sai_id'], 'info_update_form', $infoUpdationPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiSignedNotificationPending) {
                                                    foreach ($saiSignedNotificationPending as $saiNotificationPending) {
                                                        renderPendingDocCard($saiNotificationPending["customer_name"], 'SA-I', $saiNotificationPending['sai_id'], 'signed_noti_sai', $saiNotificationPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiSignedCopyPending) {
                                                    foreach ($saiSignedCopyPending as $saiScheduleCopyPending) {
                                                        renderPendingDocCard($saiScheduleCopyPending["customer_name"], 'SA-I', $saiScheduleCopyPending['sai_id'], 'signed_copy_sai', $saiScheduleCopyPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Audit Report', $saiAuditReportPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Auditor Notes', $saiAuditorNotesPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('NC and Evidences', $saiAuditEvidencePending); ?></i></span>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiAuditReportPending) {
                                                    foreach ($saiAuditReportPending as $saiAuditRptPending) {
                                                        renderPendingDocCard($saiAuditRptPending["customer_name"], 'SA-I', $saiAuditRptPending['sai_id'], 'audit_report_sai', $saiAuditRptPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiAuditorNotesPending) {
                                                    foreach ($saiAuditorNotesPending as $saiAuditNotePending) {
                                                        renderPendingDocCard($saiAuditNotePending["customer_name"], 'SA-I', $saiAuditNotePending['sai_id'], 'auditor_notes_sai', $saiAuditNotePending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiAuditEvidencePending) {
                                                    foreach ($saiAuditEvidencePending as $saiNCPending) {
                                                        renderPendingDocCard($saiNCPending["customer_name"], 'SA-I', $saiNCPending['sai_id'], 'audit_evidance_sai', $saiNCPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Certificate', $saiCertificatePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Client Feedback', $saiCustomerReviewPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiCertificatePending) {
                                                    foreach ($saiCertificatePending as $saiCertPending) {
                                                        renderPendingDocCard($saiCertPending["customer_name"], 'SA-I', $saiCertPending['sai_id'], 'certificate_print', $saiCertPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiCustomerReviewPending) {
                                                    foreach ($saiCustomerReviewPending as $saiFeedbackPending) {
                                                        renderPendingDocCard($saiFeedbackPending["customer_name"], 'SA-I', $saiFeedbackPending['sai_id'], 'customer_review_sai', $saiFeedbackPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--SA-II Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px">
                            <div class="panel-heading text-center SAii-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - SA-II</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel SAii-body" id="actionItemDisplayPanel"
                                style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Information Updation', $saiiInformationUpdatoinPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Signed Notification', $saiiSignedNotificationPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Schedule Signed Copy', $saiiSignedCopyPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiInformationUpdatoinPending) {
                                                    foreach ($saiiInformationUpdatoinPending as $saiiInfoUpdationPending) {
                                                        renderPendingDocCard($saiiInfoUpdationPending["customer_name"], 'SA-II', $saiiInfoUpdationPending['saii_id'], 'update_info_saii', $saiiInfoUpdationPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiSignedNotificationPending) {
                                                    foreach ($saiiSignedNotificationPending as $saiiNotificationPending) {
                                                        renderPendingDocCard($saiiNotificationPending["customer_name"], 'SA-II', $saiiNotificationPending['saii_id'], 'signed_noti_saii', $saiiNotificationPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiSignedCopyPending) {
                                                    foreach ($saiiSignedCopyPending as $saiiScheduleCopyPending) {
                                                        renderPendingDocCard($saiiScheduleCopyPending["customer_name"], 'SA-II', $saiiScheduleCopyPending['saii_id'], 'signed_copy_saii', $saiiScheduleCopyPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Audit Report', $saiiAuditReportPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Auditor Notes', $saiiAuditorNotesPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('NC and Evidences', $saiiAuditorEvidencePending); ?></i></span>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiAuditReportPending) {
                                                    foreach ($saiiAuditReportPending as $saiiAuditRptPending) {
                                                        renderPendingDocCard($saiiAuditRptPending["customer_name"], 'SA-II', $saiiAuditRptPending['saii_id'], 'audit_report_saii', $saiiAuditRptPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiAuditorNotesPending) {
                                                    foreach ($saiiAuditorNotesPending as $saiiAuditNotePending) {
                                                        renderPendingDocCard($saiiAuditNotePending["customer_name"], 'SA-II', $saiiAuditNotePending['saii_id'], 'auditor_notes_saii', $saiiAuditNotePending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiAuditorEvidencePending) {
                                                    foreach ($saiiAuditorEvidencePending as $saiiNCPending) {
                                                        renderPendingDocCard($saiiNCPending["customer_name"], 'SA-II', $saiiNCPending['saii_id'], 'audit_evidences_saii', $saiiNCPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Certificate', $saiiCertificatePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Client Feedback', $saiiFeedbackPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiCertificatePending) {
                                                    foreach ($saiiCertificatePending as $saiiCertPending) {
                                                        renderPendingDocCard($saiiCertPending["customer_name"], 'SA-II', $saiiCertPending['saii_id'], 'certificate_print', $saiiCertPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiFeedbackPending) {
                                                    foreach ($saiiFeedbackPending as $saiiFeedbackItem) {
                                                        renderPendingDocCard($saiiFeedbackItem["customer_name"], 'SA-II', $saiiFeedbackItem['saii_id'], 'customer_review_saii', $saiiFeedbackItem['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--SA-RC Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px">
                            <div class="panel-heading text-center RC-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - RC</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel RC-body" id="actionItemDisplayPanel"
                                style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Signed Notification', $rcNotificationPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Signed Schedule Copy', $rcSignedSchedulePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Audit Report', $rcAuditReportPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcNotificationPending) {
                                                    foreach ($rcNotificationPending as $rcNotiPending) {
                                                        renderPendingDocCard($rcNotiPending["customer_name"], 'RC', $rcNotiPending['rc_id'], 'rc_signed_noti', $rcNotiPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcSignedSchedulePending) {
                                                    foreach ($rcSignedSchedulePending as $rcSchedulePending) {
                                                        renderPendingDocCard($rcSchedulePending["customer_name"], 'RC', $rcSchedulePending['rc_id'], 'rc_signed_copy', $rcSchedulePending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcAuditReportPending) {
                                                    foreach ($rcAuditReportPending as $rcAudRptPending) {
                                                        renderPendingDocCard($rcAudRptPending["customer_name"], 'RC', $rcAudRptPending['rc_id'], 'rc_audit_report', $rcAudRptPending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Auditor Notes', $rcAuditorNotesPending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('NC and Evidences', $rcAudiEvidencePending); ?></i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Certificate', $rcCertificatePending); ?></i></span>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcAuditorNotesPending) {
                                                    foreach ($rcAuditorNotesPending as $rcAuditNotePending) {
                                                        renderPendingDocCard($rcAuditNotePending["customer_name"], 'RC', $rcAuditNotePending['rc_id'], 'auditor_notes_rc', $rcAuditNotePending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcAudiEvidencePending) {
                                                    foreach ($rcAudiEvidencePending as $rcEvidencePending) {
                                                        renderPendingDocCard($rcEvidencePending["customer_name"], 'RC', $rcEvidencePending['rc_id'], 'rc_audit_evidence', $rcEvidencePending['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcCertificatePending) {
                                                    foreach ($rcCertificatePending as $rcCert) {
                                                        renderPendingDocCard($rcCert["customer_name"], 'RC', $rcCert['rc_id'], 'certificate_print', $rcCert['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "
                                                style="height: 28px">
                                                <span><i class="fa"><?php echo uploadingSectionTitle('Client Feedback', $rcCustomerReviewPending); ?></i></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcCustomerReviewPending) {
                                                    foreach ($rcCustomerReviewPending as $rcCustReview) {
                                                        renderPendingDocCard($rcCustReview["customer_name"], 'RC', $rcCustReview['rc_id'], 'rc_customer_review', $rcCustReview['auditors'], $commMap);
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="serviceContentCompleted">
                                                        <span>Uploading Completed</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    </div><!-- /#tabUploadingStatus -->

                    <!-- Tab 2: Auditor Communication -->
                    <div role="tabpanel" class="tab-pane" id="tabCommunication">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-primary comm-panel">
                                    <div class="panel-heading comm-panel-heading">
                                        <div class="comm-header-flex">
                                            <div>
                                                <h4 style="margin:0; font-weight:800; font-size:16px;"><i class="fa fa-comments"></i> Auditor Communication & Notes</h4>
                                                <span style="font-size:12px; opacity:0.85;">Live log of status updates, reasons, and remarks submitted by auditors</span>
                                            </div>
                                            <div class="comm-header-stats">
                                                <span class="comm-stat-badge"><i class="fa fa-check-circle" style="color:#34d399;"></i> Sent by Auditor: <?php echo count(array_filter($allCommunications, function($c){ return ($c['status'] ?? '') === 'YES'; })); ?></span>
                                                <span class="comm-stat-badge"><i class="fa fa-clock-o" style="color:#f87171;"></i> Pending with Reason: <?php echo count(array_filter($allCommunications, function($c){ return ($c['status'] ?? '') === 'NO'; })); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding:16px;">
                                        <!-- Search and Filter Bar -->
                                        <div class="row" style="margin-bottom: 14px;">
                                            <div class="col-sm-6">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                                    <input type="text" id="commSearchInput" class="form-control" placeholder="Search client, auditor, service, document or note...">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-right" style="margin-top: 2px;">
                                                <div class="btn-group" id="commFilterGroup">
                                                    <button type="button" class="btn btn-default btn-sm active" data-filter="all">All (<?php echo count($allCommunications); ?>)</button>
                                                    <button type="button" class="btn btn-default btn-sm" data-filter="YES"><i class="fa fa-check-circle text-success"></i> Sent (Yes)</button>
                                                    <button type="button" class="btn btn-default btn-sm" data-filter="NO"><i class="fa fa-clock-o text-danger"></i> Pending (No)</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Communications Table -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover comm-table" id="commTable">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40px; text-align:center;">#</th>
                                                        <th style="width:200px;">Client Name</th>
                                                        <th style="width:80px; text-align:center;">Service</th>
                                                        <th style="width:160px;">Pending Document</th>
                                                        <th style="width:140px;">Auditor</th>
                                                        <th style="width:180px; text-align:center;">Status</th>
                                                        <th>Auditor Note / Reason</th>
                                                        <th style="width:140px; text-align:center;">Updated At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($allCommunications)) { ?>
                                                        <tr class="empty-comm-row">
                                                            <td colspan="8" class="text-center" style="padding: 35px; color: #64748b;">
                                                                <i class="fa fa-comments-o fa-3x" style="display:block; margin-bottom: 10px; opacity:0.5;"></i>
                                                                <strong>No auditor communication recorded yet.</strong><br>
                                                                When auditors submit Yes/No remarks from the Pending Documents portal, their notes and reasons will appear here in real-time.
                                                            </td>
                                                        </tr>
                                                    <?php } else {
                                                        $cIdx = 1;
                                                        foreach ($allCommunications as $c) {
                                                            $isYes = ($c['status'] === 'YES');
                                                            $timeStr = !empty($c['updated_at']) ? date('d M Y, h:i A', strtotime($c['updated_at'])) : '-';
                                                    ?>
                                                        <tr class="comm-row" data-status="<?php echo htmlspecialchars($c['status']); ?>">
                                                            <td style="text-align:center; color:#64748b; font-weight:600; vertical-align:middle;"><?php echo $cIdx++; ?></td>
                                                            <td style="vertical-align:middle;">
                                                                <strong style="color:#0f172a; font-size:13.5px; display:block;"><?php echo htmlspecialchars($c['customer_name']); ?></strong>
                                                                <?php if (!empty($c['customer_id'])) { ?>
                                                                    <span style="color:#64748b; font-size:11px; display:inline-block !important;">Client ID: <?php echo htmlspecialchars($c['customer_id']); ?></span>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="text-align:center; vertical-align:middle;">
                                                                <span class="comm-service-badge"><?php echo htmlspecialchars($c['service_type']); ?></span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <span class="comm-doc-name"><i class="fa fa-file-text-o" style="color:#64748b; margin-right:4px;"></i><?php echo htmlspecialchars($c['document_name']); ?></span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <span style="font-weight:600; color:#334155; font-size:12.5px; display:inline-flex !important; align-items:center; gap:4px;">
                                                                    <i class="fa fa-user" style="color:#64748b;"></i> <?php echo htmlspecialchars($c['auditor_name']); ?>
                                                                </span>
                                                            </td>
                                                            <td style="text-align:center; vertical-align:middle;">
                                                                <?php if ($isYes) { ?>
                                                                    <span class="comm-status-badge sent"><i class="fa fa-check-circle"></i> Send by <?php echo htmlspecialchars($c['auditor_name']); ?></span>
                                                                <?php } else { ?>
                                                                    <span class="comm-status-badge pending"><i class="fa fa-clock-o"></i> Pending from <?php echo htmlspecialchars($c['auditor_name']); ?></span>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <?php if (!empty($c['note'])) { ?>
                                                                    <div class="comm-note-card <?php echo $isYes ? 'yes-card' : 'no-card'; ?>">
                                                                        <div class="comm-note-title">
                                                                            <i class="fa <?php echo $isYes ? 'fa-check-circle text-success' : 'fa-info-circle text-danger'; ?>"></i>
                                                                            <span><?php echo $isYes ? 'Auditor Note (Sent):' : 'Pending Reason:'; ?></span>
                                                                        </div>
                                                                        <div class="comm-note-body"><?php echo nl2br(htmlspecialchars($c['note'])); ?></div>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <span style="color:#94a3b8; font-style:italic; font-size:12px; display:inline-block !important;">No remark entered</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="text-align:center; font-size:11.5px; color:#64748b; vertical-align:middle; white-space:nowrap;"><?php echo $timeStr; ?></td>
                                                        </tr>
                                                    <?php } } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /#tabCommunication -->
                </div><!-- /.tab-content -->
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">

    $(document).ready(function () {

        $(".CR-Head").click(function () {
            //                alert($(this).attr("data-id"));
            $(".CR-body").toggle();
            //            $(".cadates" + svc).toggle();
        });

        $(".CA-Head").click(function () {
            //                alert($(this).attr("data-id"));
            $(".CA-body").toggle();
            //            $(".cadates" + svc).toggle();
        });

        $(".SAi-Head").click(function () {
            //                alert($(this).attr("data-id"));
            $(".SAi-body").toggle();
            //            $(".cadates" + svc).toggle();
        });

        $(".SAii-Head").click(function () {
            //                alert($(this).attr("data-id"));
            $(".SAii-body").toggle();
            //            $(".cadates" + svc).toggle();
        });

        $(".RC-Head").click(function () {
            //                alert($(this).attr("data-id"));
            $(".RC-body").toggle();
            //            $(".cadates" + svc).toggle();
        });

        // Communication Tab Search & Filter
        $("#commSearchInput").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            var statusFilter = $("#commFilterGroup button.active").data("filter") || "all";
            $("#commTable tbody tr.comm-row").filter(function () {
                var matchStatus = (statusFilter === "all" || $(this).data("status") === statusFilter);
                var matchText = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(matchStatus && matchText);
            });
        });

        $("#commFilterGroup button").on("click", function () {
            $("#commFilterGroup button").removeClass("active");
            $(this).addClass("active");
            var filter = $(this).data("filter");
            var searchVal = ($("#commSearchInput").val() || "").toLowerCase();
            $("#commTable tbody tr.comm-row").filter(function () {
                var matchStatus = (filter === "all" || $(this).data("status") === filter);
                var matchText = searchVal === "" || $(this).text().toLowerCase().indexOf(searchVal) > -1;
                $(this).toggle(matchStatus && matchText);
            });
        });

        // Activate tab from URL hash if present
        if (window.location.hash) {
            $('.hero-tabs-nav a[href="' + window.location.hash + '"]').tab('show');
        }

        // Segmented tab active class handler
        $('.hero-tabs-nav a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $('.hero-tabs-nav li').removeClass('active');
            $(this).closest('li').addClass('active');
        });

    });

</script>

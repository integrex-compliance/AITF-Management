<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Document Upload";
    include 'header.php';
    include 'dbConnect.php';

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
    $caSignedNotification1 = "SELECT sca.ca_id, service, customer_id, signed_notification, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_notification like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caSignedNotification1);
    $statement->execute();
    $caSignedNotification1Pending = $statement->fetchAll();

    $caStage1Schedule = "SELECT sca.ca_id, service, customer_id, signed_copy, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_copy like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage1Schedule);
    $statement->execute();
    $caStage1SchedulePending = $statement->fetchAll();

    $caStage1AuditReport = "SELECT sca.ca_id, service, customer_id, audit_report_I, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_report_I like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage1AuditReport);
    $statement->execute();
    $caStage1AuditReportPending = $statement->fetchAll();

    $caStage2SgnedCopy = "SELECT sca.ca_id, service, customer_id, signed_notification_II, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE signed_notification_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caStage2SgnedCopy);
    $statement->execute();
    $caStage2SgnedCopyPending = $statement->fetchAll();

    $caAuditReport2 = "SELECT sca.ca_id, service, customer_id, audit_report_II, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_report_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditReport2);
    $statement->execute();
    $caAuditReport2Pending = $statement->fetchAll();

    $caAuditorNotes = "SELECT sca.ca_id, service, customer_id, auditor_notes, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE auditor_notes like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditorNotes);
    $statement->execute();
    $caAuditorNotesPending = $statement->fetchAll();

    $caAuditNC = "SELECT sca.ca_id, service, customer_id, audit_nc_II, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE audit_nc_II like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caAuditNC);
    $statement->execute();
    $caAuditNCPending = $statement->fetchAll();

    $caCertificate = "SELECT sca.ca_id, service, customer_id, certificate_print, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caCertificate);
    $statement->execute();
    $caCertificatePending = $statement->fetchAll();

    $caCustomerFeedback = "SELECT sca.ca_id, service, customer_id, customer_satisfaction, ci.customer_name FROM service_type_ca sca LEFT JOIN customer_info ci on sca.customer_id = ci.id WHERE customer_satisfaction like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($caCustomerFeedback);
    $statement->execute();
    $caCustomerFeedbackPending = $statement->fetchAll();

    //    Service SA-I
    
    $saiInformationUpdatoin = "SELECT sai.sai_id, service, customer_id, info_update_form, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE info_update_form like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiInformationUpdatoin);
    $statement->execute();
    $saiInformationUpdatoinPending = $statement->fetchAll();

    $saiSignedNotification = "SELECT sai.sai_id, service, customer_id, signed_noti_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE signed_noti_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiSignedNotification);
    $statement->execute();
    $saiSignedNotificationPending = $statement->fetchAll();

    $saiSignedCopy = "SELECT sai.sai_id, service, customer_id, signed_copy_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE signed_copy_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiSignedCopy);
    $statement->execute();
    $saiSignedCopyPending = $statement->fetchAll();

    $saiAuditReport = "SELECT sai.sai_id, service, customer_id, audit_report_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE audit_report_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditReport);
    $statement->execute();
    $saiAuditReportPending = $statement->fetchAll();

    $saiAuditorNotes = "SELECT sai.sai_id, service, customer_id, auditor_notes_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE auditor_notes_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditorNotes);
    $statement->execute();
    $saiAuditorNotesPending = $statement->fetchAll();

    $saiAuditEvidence = "SELECT sai.sai_id, service, customer_id, audit_evidance_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE audit_evidance_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiAuditEvidence);
    $statement->execute();
    $saiAuditEvidencePending = $statement->fetchAll();

    $saiCertificate = "SELECT sai.sai_id, service, customer_id, certificate_print, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiCertificate);
    $statement->execute();
    $saiCertificatePending = $statement->fetchAll();

    $saiCustomerReview = "SELECT sai.sai_id, service, customer_id, customer_review_sai, ci.customer_name FROM service_type_sai sai LEFT JOIN customer_info ci on sai.customer_id = ci.id WHERE customer_review_sai like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiCustomerReview);
    $statement->execute();
    $saiCustomerReviewPending = $statement->fetchAll();

    //    service type SA-II
    
    $saiiInformationUpdatoin = "SELECT saii.saii_id, service, customer_id, update_info_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE update_info_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiInformationUpdatoin);
    $statement->execute();
    $saiiInformationUpdatoinPending = $statement->fetchAll();

    $saiiSignedNotification = "SELECT saii.saii_id, service, customer_id, signed_noti_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE signed_noti_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiSignedNotification);
    $statement->execute();
    $saiiSignedNotificationPending = $statement->fetchAll();

    $saiiSignedCopy = "SELECT saii.saii_id, service, customer_id, signed_copy_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE signed_copy_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiSignedCopy);
    $statement->execute();
    $saiiSignedCopyPending = $statement->fetchAll();

    $saiiAuditReport = "SELECT saii.saii_id, service, customer_id, audit_report_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE audit_report_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditReport);
    $statement->execute();
    $saiiAuditReportPending = $statement->fetchAll();

    $saiiAuditorNotes = "SELECT saii.saii_id, service, customer_id, auditor_notes_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE auditor_notes_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditorNotes);
    $statement->execute();
    $saiiAuditorNotesPending = $statement->fetchAll();

    $saiiAuditorEvidence = "SELECT saii.saii_id, service, customer_id, audit_evidences_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE audit_evidences_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiAuditorEvidence);
    $statement->execute();
    $saiiAuditorEvidencePending = $statement->fetchAll();

    $saiiCertificate = "SELECT saii.saii_id, service, customer_id, certificate_print, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiCertificate);
    $statement->execute();
    $saiiCertificatePending = $statement->fetchAll();

    $saiiFeedback = "SELECT saii.saii_id, service, customer_id, customer_review_saii, ci.customer_name FROM service_type_saii saii LEFT JOIN customer_info ci on saii.customer_id = ci.id WHERE customer_review_saii like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($saiiFeedback);
    $statement->execute();
    $saiiFeedbackPending = $statement->fetchAll();

    //    RC service
    
    $rcNotification = "SELECT rc.rc_id, service, rc_customer, rc_signed_noti, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_signed_noti like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcNotification);
    $statement->execute();
    $rcNotificationPending = $statement->fetchAll();

    $rcSignedSchedule = "SELECT rc.rc_id, service, rc_customer, rc_signed_copy, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_signed_copy like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcSignedSchedule);
    $statement->execute();
    $rcSignedSchedulePending = $statement->fetchAll();

    $rcAuditReport = "SELECT rc.rc_id, service, rc_customer, rc_audit_report, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_audit_report like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAuditReport);
    $statement->execute();
    $rcAuditReportPending = $statement->fetchAll();

    $rcAuditorNotes = "SELECT rc.rc_id, service, rc_customer, auditor_notes_rc, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE auditor_notes_rc like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAuditorNotes);
    $statement->execute();
    $rcAuditorNotesPending = $statement->fetchAll();

    $rcAudiEvidence = "SELECT rc.rc_id, service, rc_customer, rc_audit_evidence, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_audit_evidence like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcAudiEvidence);
    $statement->execute();
    $rcAudiEvidencePending = $statement->fetchAll();

    $rcCertificate = "SELECT rc.rc_id, service, rc_customer, certificate_print, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE certificate_print like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
    $statement = $db->prepare($rcCertificate);
    $statement->execute();
    $rcCertificatePending = $statement->fetchAll();

    $rcCustomerReview = "SELECT rc.rc_id, service, rc_customer, rc_customer_review, ci.customer_name FROM service_type_rc rc LEFT JOIN customer_info ci on rc.rc_customer = ci.id WHERE rc_customer_review like '%scanning%' and is_deleted = 'NO' ORDER BY ci.customer_name";
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

    if (!function_exists('uploadingSectionTitle')) {
        function uploadingSectionTitle($label, $items)
        {
            return $label . ' <span class="uploading-section-count">(' . count($items) . ')</span>';
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
                </style>
                <br>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <h4 style="color: #053c6d; text-align: center">Uploading Status</h4>
                            <p style="text-align: center; color: red; font-weight: bold; margin-bottom: 10px;">Total pending document = <?php echo $totalPendingDocuments; ?></p>
                        </div>
                    </div>
                </div>

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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $notificationPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caStage1SchedulePending) {
                                                    foreach ($caStage1SchedulePending as $schedulePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $schedulePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caStage1AuditReportPending) {
                                                    foreach ($caStage1AuditReportPending as $auditReport1Pending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $auditReport1Pending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $stage2signCopyPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caAuditReport2Pending) {
                                                    foreach ($caAuditReport2Pending as $auditReport2Pending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $auditReport2Pending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caAuditorNotesPending) {
                                                    foreach ($caAuditorNotesPending as $auditNotesPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $auditNotesPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $auditNCPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caCertificatePending) {
                                                    foreach ($caCertificatePending as $certificatePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $certificatePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($caCustomerFeedbackPending) {
                                                    foreach ($caCustomerFeedbackPending as $customerFeedbackPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $customerFeedbackPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $infoUpdationPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiSignedNotificationPending) {
                                                    foreach ($saiSignedNotificationPending as $saiNotificationPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiNotificationPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiSignedCopyPending) {
                                                    foreach ($saiSignedCopyPending as $saiScheduleCopyPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiScheduleCopyPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiAuditRptPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiAuditorNotesPending) {
                                                    foreach ($saiAuditorNotesPending as $saiAuditNotePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiAuditNotePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiAuditEvidencePending) {
                                                    foreach ($saiAuditEvidencePending as $saiNCPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiNCPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiCertPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiCustomerReviewPending) {
                                                    foreach ($saiCustomerReviewPending as $saiFeedbackPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiFeedbackPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiInfoUpdationPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiSignedNotificationPending) {
                                                    foreach ($saiiSignedNotificationPending as $saiiNotificationPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiNotificationPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiSignedCopyPending) {
                                                    foreach ($saiiSignedCopyPending as $saiiScheduleCopyPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiScheduleCopyPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiAuditRptPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiAuditorNotesPending) {
                                                    foreach ($saiiAuditorNotesPending as $saiiAuditNotePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiAuditNotePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiAuditorEvidencePending) {
                                                    foreach ($saiiAuditorEvidencePending as $saiiNCPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiNCPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiCertPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($saiiFeedbackPending) {
                                                    foreach ($saiiFeedbackPending as $saiiFeedbackPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $saiiFeedbackPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcNotiPending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcSignedSchedulePending) {
                                                    foreach ($rcSignedSchedulePending as $rcSchedulePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcSchedulePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcAuditReportPending) {
                                                    foreach ($rcAuditReportPending as $rcAudRptPending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcAudRptPending["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcAuditNotePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcAudiEvidencePending) {
                                                    foreach ($rcAudiEvidencePending as $rcEvidencePending) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcEvidencePending["customer_name"] ?></span>
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
                                            <div class="col-sm-4">
                                                <?php
                                                if ($rcCertificatePending) {
                                                    foreach ($rcCertificatePending as $rcCert) {
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcCert["customer_name"] ?></span>
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
                                                        ?>
                                                        <div class="serviceContentwaiting">
                                                            <span><?php echo $rcCustReview["customer_name"] ?></span>
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

    });

</script>

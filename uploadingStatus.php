<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Document Upload";
    include 'header.php';
    include 'dbConnect.php';

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
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container" >
                <br>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" >
                            <h4 style="color: #053c6d; text-align: center">Uploading Status</h4>
                        </div>
                    </div>
                </div>

                <!--CA-Service-->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary" style="min-height: 40px" >
                            <div class="panel-heading text-center CR-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">Contract Review</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel CR-body" id="actionItemDisplayPanel" style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-6 text-center btn qms_complete_btn">
                                                <span><i class="fa">Application Form</i></span>
                                            </div>
                                            <div class="col-xs-6  text-center btn qms_complete_btn">
                                                <span><i class="fa">Certification Agreement</i></span>
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
                        <div class="panel panel-primary" style="min-height: 40px" >
                            <div class="panel-heading text-center CA-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - CA</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel CA-body" id="actionItemDisplayPanel" style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa">Stage-1 Notification</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa">Stage-1 Schedule</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Stage-1 Audit Report</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Stage-2 Notification</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Stage-2 Audit Report</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Stage-2 Auditor Notes</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">NC and Evidances</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Certificate</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Client Feedback</i></span>
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
                        <div class="panel panel-primary" style="min-height: 40px" >
                            <div class="panel-heading text-center SAi-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - SA-I</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel SAi-body" id="actionItemDisplayPanel" style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Information Updation</i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa">Signed Notification</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa">Schedule Signed Copy</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Audit Report</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Auditor Notes</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">NC and Evidences</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Certificate</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Client Feedback</i></span>
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
                                                if ($saiCertificatePending) {
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
                        <div class="panel panel-primary" style="min-height: 40px" >
                            <div class="panel-heading text-center SAii-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - SA-II</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel SAii-body" id="actionItemDisplayPanel" style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Information Updation</i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa">Signed Notification</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa">Schedule Signed Copy</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Audit Report</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Auditor Notes</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">NC and Evidences</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Certificate</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Client Feedback</i></span>
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
                        <div class="panel panel-primary" style="min-height: 40px" >
                            <div class="panel-heading text-center RC-Head" style="background: #053c6d; height: 50px;">
                                <h4 style="color: white">SERVICE TYPE - RC</h4>
                            </div>
                            <div class="panel-rightbody homePagePanel RC-body" id="actionItemDisplayPanel" style="display: none">
                                <div class="row event-row" id="dummyActionItemRow">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="row" style="">
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Signed Notification</i></span>
                                            </div>
                                            <div class="col-xs-4 text-center btn qms_complete_btn">
                                                <span><i class="fa">Signed Schedule Copy</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn">
                                                <span><i class="fa">Audit Report</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Auditor Notes</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">NC and Evidences</i></span>
                                            </div>
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Certificate</i></span>
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
                                            <div class="col-xs-4  text-center btn qms_complete_btn "  style="height: 28px">
                                                <span><i class="fa">Client Feedback</i></span>
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
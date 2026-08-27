<div id="wrapper" class="active">
<?php
$pageIdentifier = "Application Received";
include 'header.php';
include 'dbConnect.php';
$isAuditorView = isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor';
$isTopMgmt = (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role()) || (isset($_SESSION['role']) && trim($_SESSION['role']) === 'Top Mgmt');
?>
<?php if ($isAuditorView): ?>
<style>
    /* Hide the left sidebar menu completely for auditors */
    #sidebar-wrapper { display: none !important; }
    #wrapper, #wrapper.active { padding-left: 0 !important; }
    
    /* Disable delete for auditors */
    .fa-trash, .btn-danger, [onclick*="delete"], .arv-btn-del { 
        pointer-events: none !important; 
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }
</style>
<?php endif; ?>
<?php

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
}

$application = null;
$updateSuccess = '';
$updateError = '';

if ($id > 0 && isset($db) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $postId = (int) ($_POST['id'] ?? $id);
    $id = $postId;
    if ($_POST['action'] === 'update_review') {
        $review = trim($_POST['auditor_review'] ?? '');
        try {
            $stmt = $db->prepare("UPDATE online_audit_applications SET auditor_review = :review WHERE id = :id");
            $stmt->bindParam(':review', $review, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $updateSuccess = 'Review updated successfully.';
            if (isset($application) && is_array($application)) {
                $application['auditor_review'] = $review;
            }
        } catch (Exception $e) {
            $updateError = 'Unable to update review right now.';
        }
    } elseif ($_POST['action'] === 'delete_app') {
        if (!$isTopMgmt) {
            $updateError = 'You are not allowed to perform this action. Only Top Management can delete the form.';
        } else {
            try {
                $stmt = $db->prepare("DELETE FROM online_audit_applications WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo "<script>window.location.href='applicationReceived.php?deleted=1';</script>";
                exit;
            } catch (Exception $e) {
                $updateError = 'Unable to delete application.';
            }
        }
    }
}

if ($id > 0 && isset($db)) {
    try {
        $stmt = $db->prepare("SELECT * FROM online_audit_applications WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($application && isset($application['read_flag']) && (int)$application['read_flag'] === 0) {
            try {
                $markRead = $db->prepare("UPDATE online_audit_applications SET read_flag = 1 WHERE id = :id");
                $markRead->bindParam(':id', $id, PDO::PARAM_INT);
                $markRead->execute();
                $application['read_flag'] = 1;
            } catch (Exception $ignored) {
            }
        }
    } catch (Exception $e) {
        $application = null;
    }
}

$createdAtDisplay = '';
if ($application && !empty($application['created_at'])) {
    try {
        $dt = new DateTimeImmutable($application['created_at'], new DateTimeZone('Asia/Kolkata'));
        $createdAtDisplay = $dt->format('d M Y, h:i A');
    } catch (Exception $e) {
        $createdAtDisplay = $application['created_at'];
    }
}

$supportAttachments = [];
if ($application) {
    $supportAttachmentDefs = [
        ['label' => 'Process flow diagram', 'path_key' => 'process_flow_attachment_path', 'name_key' => 'process_flow_attachment_name'],
        ['label' => 'Organisation brochure', 'path_key' => 'organisation_brochure_attachment_path', 'name_key' => 'organisation_brochure_attachment_name'],
        ['label' => 'Customer specific requirements', 'path_key' => 'customer_requirements_attachment_path', 'name_key' => 'customer_requirements_attachment_name'],
    ];
    foreach ($supportAttachmentDefs as $attachmentDef) {
        $path = trim((string)($application[$attachmentDef['path_key']] ?? ''));
        if ($path === '') {
            continue;
        }
        $supportAttachments[] = [
            'label' => $attachmentDef['label'],
            'path' => $path,
            'name' => trim((string)($application[$attachmentDef['name_key']] ?? '')) ?: basename($path),
        ];
    }
}
?>
    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid arv-container">

                <?php if (!$application) { ?>
                    <!-- No Application Found -->
                    <div class="arv-hero-header">
                        <div class="arv-hero-left">
                            <div class="arv-hero-icon"><i class="fa fa-file-text-o"></i></div>
                            <div>
                                <h1 class="arv-hero-title">Application Details</h1>
                                <div class="arv-hero-subtitle">No application found with ID #<?php echo htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo $isAuditorView ? 'auditorDashboard.php' : 'applicationReceived.php'; ?>" class="arv-btn arv-btn-back">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                <?php } else { ?>

                    <!-- Alert Messages -->
                    <?php if ($updateSuccess !== '') { ?>
                        <div class="alert alert-success" style="border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 16px;">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class="fa fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($updateSuccess, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } elseif ($updateError !== '') { ?>
                        <div class="alert alert-danger" style="border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 16px;">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class="fa fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($updateError, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>

                    <!-- Hero Header with Actions (masterCreator Style) -->
                    <div class="arv-hero-header">
                        <div class="arv-hero-left">
                            <div class="arv-hero-icon"><i class="fa fa-file-text-o"></i></div>
                            <div>
                                <h1 class="arv-hero-title">Application Details</h1>
                                <div class="arv-hero-subtitle">
                                    <strong><?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if (!empty($createdAtDisplay)) { ?>
                                        <span class="arv-sub-dot">&bull;</span>
                                        <i class="fa fa-clock-o" style="margin-right: 3px;"></i> <?php echo htmlspecialchars($createdAtDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php } ?>
                                    <span class="label label-info" style="margin-left: 8px; border-radius: 999px; font-weight: 700; padding: 3px 8px; font-size: 11px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">Audit Application</span>
                                </div>
                            </div>
                        </div>
                        <div class="arv-hero-actions">
                            <button type="button" class="arv-btn arv-btn-del" onclick="handleDeleteClick()">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            <a href="downloadOnlineApplication.php?id=<?php echo (int)$application['id']; ?>" class="arv-btn arv-btn-download">
                                <i class="fa fa-download"></i> Download
                            </a>
                            <a href="<?php echo $isAuditorView ? 'auditorDashboard.php' : 'applicationReceived.php'; ?>" class="arv-btn arv-btn-back">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <!-- Admin Assign Message (if any) -->
                    <?php if (!empty($application['admin_assign_message'])) { ?>
                        <div class="arv-message-card">
                            <div class="arv-message-title"><i class="fa fa-info-circle"></i> Message from Admin</div>
                            <p class="arv-message-body">
                                <?php echo nl2br(htmlspecialchars($application['admin_assign_message'], ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                        </div>
                    <?php } ?>

                    <!-- Auditor Review Section -->
                    <?php if (!empty($application['auditor_review']) || Utitlity::checkForAuditor() || Utitlity::checkForUserMainAdmin()) { ?>
                        <div class="arv-section-block">
                            <div class="arv-section-header">
                                <i class="fa fa-commenting-o"></i> Auditor Review & Notes
                            </div>
                            <div class="arv-section-body" style="padding: 16px 20px;">
                                <?php if (Utitlity::checkForAuditor() || Utitlity::checkForUserMainAdmin()) { ?>
                                    <form method="post" action="applicationReceivedView.php?id=<?php echo (int)$application['id']; ?>">
                                        <input type="hidden" name="id" value="<?php echo (int)$application['id']; ?>">
                                        <input type="hidden" name="action" value="update_review">
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <textarea name="auditor_review" class="form-control" rows="4" placeholder="Enter auditor review notes, feedback or remarks here..." style="border-radius: 8px; border: 1.5px solid #e2e8f0;"><?php echo htmlspecialchars($application['auditor_review'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <button type="submit" class="arv-btn arv-btn-download" style="padding: 7px 18px;">
                                            <i class="fa fa-check"></i> Save Review
                                        </button>
                                    </form>
                                <?php } else { ?>
                                    <p style="margin: 0; font-size: 13.5px; color: #334155; line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($application['auditor_review'] ?? 'No review provided yet.', ENT_QUOTES, 'UTF-8')); ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- SECTION 1: Data of Organisation -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-building"></i> 1. Data of Organisation Applying for Certification
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Organization Name</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <strong><?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contact Person & Designation</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['contact_person'] . ($application['contact_designation'] ? ' (' . $application['contact_designation'] . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['contact_person'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php echo $application['contact_designation'] ? ' <span class="text-muted">(' . htmlspecialchars($application['contact_designation'], ENT_QUOTES, 'UTF-8') . ')</span>' : ''; ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Telephone / Mobile</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <a href="tel:<?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?>" style="color: #0d4d8b; font-weight: 600; text-decoration: none;">
                                                <i class="fa fa-phone" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>E-Mail Id</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <a href="mailto:<?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?>" style="color: #0d4d8b; font-weight: 600; text-decoration: none;">
                                                <i class="fa fa-envelope-o" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Address of Site to be Audited</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['site_address'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['site_address'], ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Office Address</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['office_address'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['office_address'], ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Web-Site</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php if (!empty($application['website'])) { ?>
                                                <a href="<?php echo (strpos($application['website'], 'http') === 0 ? '' : 'https://') . htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 600; text-decoration: none;">
                                                    <i class="fa fa-globe" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted">N/A</span>
                                            <?php } ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>GSTIN Number</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['gstin'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <span style="font-family: monospace; font-weight: 700; color: #1e40af;"><?php echo htmlspecialchars($application['gstin'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 2: Company Structure -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-sitemap"></i> 2. Company Structure
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Legal Status</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_status'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['legal_status'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Branch Offices / Group Companies</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['branch_offices'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['branch_offices'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Branch Details</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['branch_details'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['branch_details'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Legal Obligations Applicable</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_obligations_applicable'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['legal_obligations_applicable'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Legal Obligations Details</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_obligations'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['legal_obligations'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Certification Requirement -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-certificate"></i> 3. Certification Requirement
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Type of Certification Requested</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['certification_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="label label-primary" style="font-size: 12px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 6px; padding: 4px 10px;">
                                                <?php echo htmlspecialchars($application['certification_type'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Standard Applied For</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['standard_applied'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <span style="font-weight: 700; color: #0d4d8b;"><?php echo htmlspecialchars($application['standard_applied'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Accreditation Requirement</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['accreditation_requirement'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['accreditation_requirement'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Scope / Activity to be Certified</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['scope_requirement'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars($application['scope_requirement'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 4: Multisite Information -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-cubes"></i> 4. Multisite Information
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Multisite</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['multisite'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Functions Performed Centrally</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite_functions'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['multisite_functions'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Sites to be Covered</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite_sites'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['multisite_sites'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 5: Employee Structure -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-users"></i> 5. Employee Structure & Personnel
                        </div>
                        <div class="arv-section-body">
                            <?php
                            $empRaw = isset($application['employee_structure']) ? $application['employee_structure'] : '';
                            $empLines = preg_split("/\r\n|\r|\n/", (string)$empRaw);
                            $deptKeys = [
                                'Top management / Administrative staff',
                                'Management / Supervisory staff',
                                'Operators',
                                'Helpers',
                                'Others (if any)'
                            ];
                            $empMatrix = [];
                            foreach ($deptKeys as $k) {
                                $empMatrix[$k] = [
                                    'gs_perm' => '', 'gs_cont' => '',
                                    's1_perm' => '', 's1_cont' => '',
                                    's2_perm' => '', 's2_cont' => '',
                                    's3_perm' => '', 's3_cont' => ''
                                ];
                            }
                            if (!empty($empLines)) {
                                foreach ($empLines as $line) {
                                    $line = trim($line);
                                    if ($line === '') {
                                        continue;
                                    }
                                    $parts = explode(' - ', $line, 2);
                                    if (count($parts) < 2) {
                                        continue;
                                    }
                                    $dept = trim($parts[0]);
                                    $rest = trim($parts[1]);
                                    if (!isset($empMatrix[$dept])) {
                                        $empMatrix[$dept] = [
                                            'gs_perm' => '', 'gs_cont' => '',
                                            's1_perm' => '', 's1_cont' => '',
                                            's2_perm' => '', 's2_cont' => '',
                                            's3_perm' => '', 's3_cont' => ''
                                        ];
                                    }
                                    $items = explode(';', $rest);
                                    foreach ($items as $item) {
                                        $item = trim($item);
                                        if ($item === '') {
                                            continue;
                                        }
                                        $pair = explode(':', $item, 2);
                                        if (count($pair) < 2) {
                                            continue;
                                        }
                                        $label = trim($pair[0]);
                                        $value = trim($pair[1]);
                                        switch ($label) {
                                            case 'General shift - Permanent':
                                                $empMatrix[$dept]['gs_perm'] = $value;
                                                break;
                                            case 'General shift - Contract':
                                                $empMatrix[$dept]['gs_cont'] = $value;
                                                break;
                                            case 'Shift I - Permanent':
                                                $empMatrix[$dept]['s1_perm'] = $value;
                                                break;
                                            case 'Shift I - Contract':
                                                $empMatrix[$dept]['s1_cont'] = $value;
                                                break;
                                            case 'Shift II - Permanent':
                                                $empMatrix[$dept]['s2_perm'] = $value;
                                                break;
                                            case 'Shift II - Contract':
                                                $empMatrix[$dept]['s2_cont'] = $value;
                                                break;
                                            case 'Shift III - Permanent':
                                                $empMatrix[$dept]['s3_perm'] = $value;
                                                break;
                                            case 'Shift III - Contract':
                                                $empMatrix[$dept]['s3_cont'] = $value;
                                                break;
                                            default:
                                                break;
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="table-responsive">
                                <table class="table arv-matrix-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="vertical-align: middle; min-width: 220px;">Department</th>
                                            <th colspan="2" style="text-align: center; background: #e0f2fe; color: #0369a1;">General Shift</th>
                                            <th colspan="2" style="text-align: center; background: #eff6ff; color: #1e40af;">Shift I</th>
                                            <th colspan="2" style="text-align: center; background: #f5f3ff; color: #6d28d9;">Shift II</th>
                                            <th colspan="2" style="text-align: center; background: #fdf2f8; color: #9d174d;">Shift III</th>
                                        </tr>
                                        <tr>
                                            <th style="text-align: center;">Perm.</th>
                                            <th style="text-align: center;">Cont.</th>
                                            <th style="text-align: center;">Perm.</th>
                                            <th style="text-align: center;">Cont.</th>
                                            <th style="text-align: center;">Perm.</th>
                                            <th style="text-align: center;">Cont.</th>
                                            <th style="text-align: center;">Perm.</th>
                                            <th style="text-align: center;">Cont.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deptKeys as $dKey) { ?>
                                        <tr>
                                            <td style="font-weight: 600; color: #334155;"><?php echo htmlspecialchars($dKey, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['gs_perm'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['gs_cont'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s1_perm'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s1_cont'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s2_perm'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s2_cont'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s3_perm'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($empMatrix[$dKey]['s3_cont'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <table class="table arv-table" style="border-top: 1px solid #e2e8f0;">
                                <tbody>
                                    <tr>
                                        <th>Onsite Installation / Temporary Sites</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['onsite_installation'] . ($application['onsite_details'] ? ' - ' . $application['onsite_details'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['onsite_installation'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php echo $application['onsite_details'] ? ' - ' . nl2br(htmlspecialchars($application['onsite_details'], ENT_QUOTES, 'UTF-8')) : ''; ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Part Time Personnel</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['part_time_personnel'] . ($application['part_time_count'] ? ' (' . $application['part_time_count'] . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['part_time_personnel'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php echo $application['part_time_count'] ? ' (' . htmlspecialchars($application['part_time_count'], ENT_QUOTES, 'UTF-8') . ')' : ''; ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Employees Excluded from Audit Scope</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['employees_excluded'] . ($application['employees_excluded_details'] ? ' - ' . $application['employees_excluded_details'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['employees_excluded'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php echo $application['employees_excluded_details'] ? ' - ' . nl2br(htmlspecialchars($application['employees_excluded_details'], ENT_QUOTES, 'UTF-8')) : ''; ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 6: Describe Your Organisation -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-industry"></i> 6. Describe Your Organisation & Operations
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Organisation Type</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['org_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['org_type'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Major Customers</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['major_customers'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['major_customers'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Main Products</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['main_products'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['main_products'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Main Raw Materials Used / Processed</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['raw_materials'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['raw_materials'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Operations Involved</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['operations'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['operations'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>External Processes</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['external_processes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['external_processes'] ?: 'N/A', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Design Responsibility</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['design_responsibility'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['design_responsibility'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 7: Other Information -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-ellipsis-h"></i> 7. Other Information & Attachments
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Other Certificate(s)</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['other_certificates'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['other_certificates'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date of System Implementation</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['system_implementation_date'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['system_implementation_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Proposed Month-Year for Audit</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['proposed_audit_month'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['proposed_audit_month'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Consultancy Services Taken</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['consultancy_services_taken'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['consultancy_services_taken'] ?: 'No', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Consultancy Details</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['consultancy_details'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($application['consultancy_details'] ?: 'None', ENT_QUOTES, 'UTF-8')); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Supporting Attachment(s)</th>
                                        <td>
                                            <?php if (!empty($supportAttachments)) { ?>
                                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                                    <?php foreach ($supportAttachments as $supportAttachment) { ?>
                                                        <a href="<?php echo htmlspecialchars($supportAttachment['path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="arv-attachment-link">
                                                            <i class="fa fa-paperclip" style="color: #0d4d8b; font-size: 14px;"></i>
                                                            <span><strong><?php echo htmlspecialchars($supportAttachment['label'], ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars($supportAttachment['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <i class="fa fa-external-link" style="margin-left: auto; color: #94a3b8; font-size: 11px;"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            <?php } else { ?>
                                                <span class="text-muted" style="font-size: 12px;"><i class="fa fa-times-circle-o" style="margin-right: 4px;"></i> No supporting attachments uploaded.</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 8: Declaration & Signature -->
                    <div class="arv-section-block">
                        <div class="arv-section-header">
                            <i class="fa fa-pencil-square-o"></i> 8. Declaration & Signature
                        </div>
                        <div class="arv-section-body">
                            <table class="table arv-table">
                                <tbody>
                                    <tr>
                                        <th>Notes & Terms</th>
                                        <td>
                                            <ul style="padding-left: 18px; margin: 0; font-size: 12px; color: #475569; line-height: 1.6;">
                                                <li>Initial audit will be conducted in two stages. Stage 2 audit date(s) will be finalised on assessment of Stage 1 audit.</li>
                                                <li>If available, please attach process flow diagram, organisation brochure and any customer specific requirements.</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Signature</th>
                                        <td>
                                            <?php if (!empty($application['sign_image_path'])) {
                                                $imgSrc = htmlspecialchars($application['sign_image_path'], ENT_QUOTES, 'UTF-8'); ?>
                                                <div style="display: inline-block; padding: 6px; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                    <img src="<?php echo $imgSrc; ?>" alt="Signature" style="max-width: 220px; max-height: 90px; object-fit: contain;">
                                                </div>
                                            <?php } else { ?>
                                                <span class="text-muted" style="font-size: 12px;">No signature uploaded.</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Signatory Name</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <strong><?php echo htmlspecialchars($application['sign_name'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Designation</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_designation'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['sign_designation'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date of Submission</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_date'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($application['sign_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Applicant Declaration</th>
                                        <td>
                                            <?php if (!empty($application['declaration_confirmed'])) { ?>
                                                <span class="label label-success" style="font-size: 12px; font-weight: 700; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 999px; padding: 4px 10px;">
                                                    <i class="fa fa-check-circle" style="margin-right: 4px;"></i> Declaration confirmed by applicant on submission
                                                </span>
                                            <?php } else { ?>
                                                <span class="label label-default" style="font-size: 12px; border-radius: 999px; padding: 4px 10px;">Not confirmed on submission</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     STYLISH DIALOG MODAL: PERMISSION DENIED (Non-Top Mgmt)
     ===================================================================== -->
<div class="modal fade stylish-dialog-modal" id="permissionDeniedModal" tabindex="-1" role="dialog" aria-labelledby="permDeniedModalLabel">
  <div class="modal-dialog" role="document" style="max-width: 440px; margin-top: 100px;">
    <div class="modal-content">
      <div class="modal-header modal-header-warning">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="permDeniedModalLabel">
          <i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Access Restricted
        </h4>
      </div>
      <div class="modal-body text-center" style="padding: 28px 24px 20px 24px;">
        <div class="dialog-icon-wrapper warning-glow">
          <div class="dialog-icon warning">
            <i class="fa fa-lock"></i>
          </div>
        </div>
        <h3 class="dialog-title" style="font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
          You are not allowed to perform this action
        </h3>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">
          Only Top Management can delete the form.
        </p>
      </div>
      <div class="modal-footer" style="text-align: center; justify-content: center; border-top: 1px solid #f1f5f9; padding: 14px 20px;">
        <button type="button" class="btn btn-dialog-close" data-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<?php if ($application) { ?>
<!-- =====================================================================
     STYLISH DIALOG MODAL: DELETE CONFIRMATION (Top Mgmt)
     ===================================================================== -->
<div class="modal fade stylish-dialog-modal" id="deleteApplicationModal" tabindex="-1" role="dialog" aria-labelledby="deleteApplicationModalLabel">
  <div class="modal-dialog" role="document" style="max-width: 480px; margin-top: 80px;">
    <div class="modal-content">
      <form method="post" action="applicationReceivedView.php?id=<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="id" value="<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="action" value="delete_app">
        <div class="modal-header modal-header-danger">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="deleteApplicationModalLabel">
            <i class="fa fa-trash-o" style="margin-right: 8px;"></i> Confirm Deletion
          </h4>
        </div>
        <div class="modal-body" style="padding: 24px 22px 18px 22px;">
          <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div class="dialog-icon-wrapper danger-glow" style="margin: 0; flex-shrink: 0;">
              <div class="dialog-icon danger">
                <i class="fa fa-exclamation-triangle"></i>
              </div>
            </div>
            <div style="flex: 1;">
              <h3 class="dialog-title" style="text-align: left; margin-top: 0; color: #991b1b;">Permanently delete application?</h3>
              <p class="dialog-subtitle" style="text-align: left; margin-bottom: 10px;">This action cannot be undone and will permanently remove this record.</p>
              
              <div class="dialog-item-box">
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
                  <i class="fa fa-building-o" style="color: #0d4d8b; margin-right: 6px;"></i> <?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                  Record ID: #<?php echo (int)$application['id']; ?> &bull; Audit Application
                </div>
              </div>

              <ul class="danger-list" style="margin-top: 10px; padding-left: 18px; color: #64748b; font-size: 12px;">
                <li>All application details will be deleted.</li>
                <li>All uploaded attachments will be removed.</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 14px 20px; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn btn-dialog-cancel" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-dialog-delete">
            <i class="fa fa-trash" style="margin-right: 6px;"></i> Yes, Delete Application
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php } ?>

<style>
/* =====================================================================
   APPLICATION RECEIVED VIEW — MASTER CREATOR DESIGN SYSTEM (TABLE VIEW)
   ===================================================================== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

body, #wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
}

.arv-container {
    max-width: 1480px;
    margin: 0 auto;
    padding: 14px 20px 60px 20px;
}

/* =====================================================================
   HERO HEADER
   ===================================================================== */
.arv-hero-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 20px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.arv-hero-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
}

.arv-hero-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.arv-hero-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 17px;
    box-shadow: 0 4px 10px rgba(13, 77, 139, 0.25);
    flex-shrink: 0;
}

.arv-hero-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
    line-height: 1.2;
}

.arv-hero-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-top: 3px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.arv-sub-dot {
    margin: 0 4px;
    color: #94a3b8;
}

.arv-hero-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Action Buttons */
.arv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none !important;
    cursor: pointer;
    border: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.arv-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.arv-btn-del {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff !important;
}

.arv-btn-download {
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    color: #ffffff !important;
}

.arv-btn-back {
    background: #ffffff;
    color: #475569 !important;
    border: 1.5px solid #cbd5e1;
}
.arv-btn-back:hover {
    background: #f1f5f9;
    color: #0f172a !important;
}

/* Message Card */
.arv-message-card {
    padding: 14px 18px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-left: 4px solid #2563eb;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.05);
}

.arv-message-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.arv-message-body {
    margin: 0;
    font-size: 13.5px;
    color: #1e3a8a;
    font-style: italic;
    line-height: 1.5;
}

/* =====================================================================
   SECTION BLOCKS (masterCreator Headers + Wells)
   ===================================================================== */
.arv-section-block {
    margin-bottom: 22px;
}

.arv-section-header {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
    border: none !important;
    border-radius: 10px 10px 0 0 !important;
    padding: 11px 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15) !important;
    display: flex;
    align-items: center;
    gap: 8px;
}

.arv-section-body {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-top: none !important;
    border-radius: 0 0 10px 10px !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
    overflow: hidden;
}

/* =====================================================================
   TABLE STYLING
   ===================================================================== */
.arv-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.arv-table th {
    width: 28%;
    min-width: 200px;
    background: #f8fafc;
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    border-right: 1px solid #f1f5f9 !important;
    padding: 11px 16px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    vertical-align: middle !important;
    letter-spacing: 0.2px;
}

.arv-table td {
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    padding: 11px 16px !important;
    font-size: 13px !important;
    color: #0f172a !important;
    vertical-align: middle !important;
    position: relative;
    padding-right: 48px !important;
    transition: background 0.15s ease;
}

.arv-table tr:last-child th,
.arv-table tr:last-child td {
    border-bottom: none !important;
}

.arv-table tr:hover td {
    background: #f8fafc;
}

/* Copy Button */
.copy-btn {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #64748b;
    border-radius: 6px;
    font-size: 11px;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    padding: 0;
}

.copy-btn:hover {
    background: #eff6ff;
    border-color: #2563eb;
    color: #2563eb;
    transform: translateY(-50%) scale(1.08);
}

.copy-btn.copied {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

/* Matrix Table */
.arv-matrix-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.arv-matrix-table th {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    letter-spacing: 0.2px;
}

.arv-matrix-table td {
    border: 1px solid #f1f5f9;
    padding: 9px 12px;
    font-size: 12.5px;
    color: #0f172a;
    transition: background 0.15s ease;
}

.arv-matrix-table tbody tr:hover td {
    background: #f8fafc;
}

/* Attachment Links */
.arv-attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #0d4d8b !important;
    text-decoration: none !important;
    font-size: 12.5px;
    font-weight: 600;
    transition: all 0.15s ease;
}

.arv-attachment-link:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #2563eb !important;
    transform: translateY(-1px);
}

/* =====================================================================
   STYLISH DIALOG MODALS
   ===================================================================== */
.stylish-dialog-modal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
    background: #ffffff;
}

.stylish-dialog-modal .modal-header {
    padding: 14px 20px;
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%) !important;
}

.modal-header-danger {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
}

.stylish-dialog-modal .modal-title {
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.2px;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    margin: 0;
}

.stylish-dialog-modal .close {
    color: #ffffff !important;
    opacity: 0.85;
    text-shadow: none;
    font-size: 22px;
    padding: 0;
    margin: 0;
    line-height: 1;
}
.stylish-dialog-modal .close:hover { opacity: 1; }

.dialog-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.dialog-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.dialog-icon.warning {
    background: #fffbeb;
    color: #d97706;
    border: 2px solid #fde68a;
}
.warning-glow {
    filter: drop-shadow(0 6px 16px rgba(245, 158, 11, 0.25));
}

.dialog-icon.danger {
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    font-size: 20px;
}
.danger-glow {
    filter: drop-shadow(0 6px 16px rgba(239, 68, 68, 0.25));
}

.dialog-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
    letter-spacing: -0.01em;
}

.dialog-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0 0 14px 0;
    font-weight: 500;
}

.dialog-notice-box {
    background: #fff8f8;
    border: 1px solid #fee2e2;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: 12px 14px;
    text-align: left;
    margin-bottom: 16px;
}

.notice-header {
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #b91c1c;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notice-text {
    font-size: 12.5px;
    color: #7f1d1d;
    line-height: 1.5;
}

.dialog-role-badges {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 9px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.role-badge-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.role-lbl {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.4px;
}

.role-val {
    font-size: 11.5px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 999px;
}

.role-val.user-role {
    background: #e2e8f0;
    color: #334155;
}

.role-val.req-role {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.role-divider {
    color: #94a3b8;
    font-size: 12px;
}

.dialog-item-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
}

.btn-dialog-close {
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    padding: 8px 28px;
    box-shadow: 0 4px 12px rgba(13, 77, 139, 0.25);
    transition: all 0.15s ease;
}
.btn-dialog-close:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(13, 77, 139, 0.35);
}

.btn-dialog-cancel {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    font-weight: 700;
    font-size: 13px;
    padding: 7px 16px;
    border-radius: 8px;
    transition: all 0.15s;
}
.btn-dialog-cancel:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.btn-dialog-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 13px;
    padding: 7px 18px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    transition: all 0.15s;
}
.btn-dialog-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
}

/* =====================================================================
   DARK MODE SUPPORT
   ===================================================================== */
html.theme-dark {
    --hpd-bg: #0a0e14;
    --hpd-surface: #111722;
    --hpd-surface-2: #161d2b;
    --hpd-border: #232c3d;
    --hpd-border-2: #2e3a4f;
    --hpd-text: #dbe3f0;
    --hpd-text-dim: #93a1b8;
    --hpd-text-faint: #6b7890;
    --hpd-accent: #4a9eff;
}

html.theme-dark body, 
html.theme-dark #page-content-wrapper,
html.theme-dark .page-content.inset {
    background: var(--hpd-bg) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .arv-hero-header {
    background: linear-gradient(135deg, #111722 0%, #0d131e 100%);
    border-color: var(--hpd-border);
}
html.theme-dark .arv-hero-title { color: #f8fafc; }
html.theme-dark .arv-btn-back {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
    color: var(--hpd-text) !important;
}

html.theme-dark .arv-section-header {
    background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
    border-color: var(--hpd-border-2);
}

html.theme-dark .arv-section-body {
    background: var(--hpd-surface) !important;
    border-color: var(--hpd-border) !important;
}

html.theme-dark .arv-table th {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border) !important;
    color: var(--hpd-text-dim) !important;
}

html.theme-dark .arv-table td {
    border-color: var(--hpd-border) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .arv-table tr:hover td {
    background: var(--hpd-surface-2);
}

html.theme-dark .copy-btn {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border-2);
    color: var(--hpd-text-dim);
}

html.theme-dark .arv-matrix-table th {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
    color: var(--hpd-text-dim);
}

html.theme-dark .arv-matrix-table td {
    border-color: var(--hpd-border);
    color: var(--hpd-text);
}

html.theme-dark .arv-matrix-table tbody tr:hover td {
    background: var(--hpd-surface-2);
}

html.theme-dark .arv-attachment-link {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
    color: #60a5fa !important;
}

html.theme-dark .stylish-dialog-modal .modal-content {
    background: var(--hpd-surface);
    border: 1px solid var(--hpd-border);
}

html.theme-dark .stylish-dialog-modal .modal-body {
    background: var(--hpd-surface);
    color: var(--hpd-text);
}

html.theme-dark .dialog-title { color: #f8fafc; }
html.theme-dark .dialog-subtitle { color: var(--hpd-text-dim); }

html.theme-dark .dialog-notice-box {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
}
html.theme-dark .notice-text { color: #fca5a5; }

html.theme-dark .dialog-role-badges,
html.theme-dark .dialog-item-box {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
}

html.theme-dark .stylish-dialog-modal .modal-footer {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
}

html.theme-dark .btn-dialog-cancel {
    background: var(--hpd-surface);
    border-color: var(--hpd-border-2);
    color: var(--hpd-text);
}
</style>

<script>
var isTopMgmt = <?php echo $isTopMgmt ? 'true' : 'false'; ?>;

function handleDeleteClick() {
    if (!isTopMgmt) {
        $('#permissionDeniedModal').modal('show');
        return false;
    }
    $('#deleteApplicationModal').modal('show');
}

(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.copy-btn');
        if (!btn) return;
        var cell = btn.closest('td');
        if (!cell) return;
        var text = cell.getAttribute('data-copy') || '';
        if (!text) return;
        var textarea = document.createElement('textarea');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try { document.execCommand('copy'); } catch (err) {}
        document.body.removeChild(textarea);
        btn.classList.add('copied');
        var icon = btn.querySelector('i');
        if (icon) {
            icon.className = 'fa fa-check';
        }
        setTimeout(function () { 
            btn.classList.remove('copied'); 
            if (icon) {
                icon.className = 'fa fa-clone';
            }
        }, 1000);
    });
})();
</script>

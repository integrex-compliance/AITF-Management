<div id="wrapper" class="active">
<?php
$pageIdentifier = "Application Received";
include 'header.php';
include 'dbConnect.php';

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
    if ($_POST['action'] === 'update_app') {
        $org_name = trim($_POST['org_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $contact_designation = trim($_POST['contact_designation'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $site_address = trim($_POST['site_address'] ?? '');
        $office_address = trim($_POST['office_address'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $gstin = trim($_POST['gstin'] ?? '');

        if ($org_name === '' || $contact_person === '' || $phone === '' || $email === '' || $site_address === '' || $office_address === '') {
            $updateError = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $updateError = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE online_audit_applications SET
                    org_name = :org_name,
                    site_address = :site_address,
                    office_address = :office_address,
                    contact_person = :contact_person,
                    contact_designation = :contact_designation,
                    phone = :phone,
                    email = :email,
                    website = :website,
                    gstin = :gstin
                WHERE id = :id");
                $stmt->bindParam(':org_name', $org_name, PDO::PARAM_STR);
                $stmt->bindParam(':site_address', $site_address, PDO::PARAM_STR);
                $stmt->bindParam(':office_address', $office_address, PDO::PARAM_STR);
                $stmt->bindParam(':contact_person', $contact_person, PDO::PARAM_STR);
                $stmt->bindParam(':contact_designation', $contact_designation, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':website', $website, PDO::PARAM_STR);
                $stmt->bindParam(':gstin', $gstin, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $updateSuccess = 'Application details updated successfully.';
            } catch (Exception $e) {
                $updateError = 'Unable to update application right now. Please try again.';
            }
        }
    } elseif ($_POST['action'] === 'delete_app') {
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
            <div class="container" >
                <br>
                <br>
                <style>
                    .app-detail-wrapper {
                        max-width: 1000px;
                        margin: 0 auto;
                    }
                    .app-detail-card {
                        background: #ffffff;
                        border-radius: 14px;
                        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.10);
                        border: 1px solid #e5e7eb;
                        border-top: 4px solid #2563eb;
                        padding: 18px 20px 16px 20px;
                    }
                    .app-detail-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 12px;
                    }
                    .app-detail-header-left {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .app-detail-actions {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }
                    .app-detail-header-icon {
                        width: 32px;
                        height: 32px;
                        border-radius: 9999px;
                        background: linear-gradient(135deg, #2563eb, #1d4ed8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #ffffff;
                        box-shadow: 0 6px 12px rgba(37, 99, 235, 0.45);
                    }
                    .app-detail-header-icon .fa {
                        font-size: 15px;
                    }
                    .app-detail-title {
                        margin: 0;
                        font-size: 18px;
                        font-weight: 600;
                        color: #111827;
                    }
                    .app-detail-subtitle {
                        margin-top: 2px;
                        font-size: 13px;
                        color: #6b7280;
                    }
                    .app-detail-back {
                        font-size: 12px;
                        padding: 4px 10px;
                    }
                    .app-detail-download {
                        font-size: 12px;
                        padding: 4px 10px;
                    }
                    .app-detail-download i {
                        margin-right: 4px;
                    }
                    .app-section-title {
                        margin-top: 16px;
                        margin-bottom: 6px;
                        font-size: 13px;
                        font-weight: 600;
                        color: #1f2937;
                        padding: 4px 8px;
                        border-left: 3px solid #2563eb;
                        background: #f3f4f6;
                        border-radius: 3px;
                    }
                    .app-detail-table {
                        background-color: #ffffff;
                        margin-bottom: 8px;
                    }
                    .app-detail-table th {
                        width: 32%;
                        background: #f9fafb;
                        font-weight: 500;
                        font-size: 11px;
                        color: #4b5563;
                    }
                    .app-detail-table td {
                        font-size: 12px;
                        color: #111827;
                        position: relative;
                        padding-right: 44px;
                    }
                    .app-detail-table tbody tr:nth-child(even) td {
                        background-color: #f9fafb;
                    }
                    .app-detail-table tbody tr:hover td {
                        background-color: #eff6ff;
                    }
                    .app-detail-table td.app-field {
                        padding-right: 44px;
                    }
                    .copy-btn {
                        position: absolute;
                        top: 50%;
                        right: 14px;
                        transform: translateY(-50%);
                        border: 1px solid #d1d5db;
                        background: #f9fafb;
                        color: #6b7280;
                        border-radius: 9999px;
                        font-size: 11px;
                        width: 22px;
                        height: 22px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        line-height: 1.2;
                    }
                    .copy-btn .fa {
                        margin: 0;
                    }
                    .copy-btn.copied {
                        background: #22c55e;
                        border-color: #16a34a;
                        color: #ffffff;
                    }
                </style>
                <?php if (!$application) { ?>
                    <div class="row clearfix">
                        <div class="col-sm-12 main_content">
                            <div class="app-detail-wrapper">
                            <div class="app-detail-card">
                                <div class="app-detail-header">
                                    <div class="app-detail-header-left">
                                        <div class="app-detail-header-icon"><i class="fa fa-file-text-o"></i></div>
                                        <div>
                                            <h3 class="app-detail-title">Application Details</h3>
                                            <div class="app-detail-subtitle">No application found</div>
                                        </div>
                                    </div>
                                    <a href="applicationReceived.php" class="btn btn-default btn-sm app-detail-back">&laquo; Back</a>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="row clearfix">
                        <div class="col-sm-12 main_content">
                            <div class="app-detail-wrapper">
                            <div class="app-detail-card">
                                <div class="app-detail-header">
                                    <div class="app-detail-header-left">
                                        <div class="app-detail-header-icon"><i class="fa fa-file-text-o"></i></div>
                                        <div>
                                            <h3 class="app-detail-title">Application Details</h3>
                                            <div class="app-detail-subtitle">
                                                <?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($createdAtDisplay)) { ?>
                                                    &bull;
                                                    <?php echo htmlspecialchars($createdAtDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="app-detail-actions">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editApplicationModal"><i class="fa fa-pencil"></i> Edit</button>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteApplicationModal"><i class="fa fa-trash"></i> Delete</button>
                                        <a href="downloadOnlineApplication.php?id=<?php echo (int)$application['id']; ?>" class="btn btn-primary btn-sm app-detail-download"><i class="fa fa-download"></i>Download</a>
                                        <a href="applicationReceived.php" class="btn btn-default btn-sm app-detail-back">&laquo; Back</a>
                                    </div>
                                </div>

                                <?php if ($updateSuccess !== '') { ?>
                                    <div class="alert alert-success" style="padding:8px 12px; margin-bottom:12px;">
                                        <?php echo htmlspecialchars($updateSuccess, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php } elseif ($updateError !== '') { ?>
                                    <div class="alert alert-danger" style="padding:8px 12px; margin-bottom:12px;">
                                        <?php echo htmlspecialchars($updateError, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php } ?>

                                <h4 class="app-section-title">1. Data of Organisation Applying for Certification</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Organization name</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Contact person with designation</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['contact_person'] . ($application['contact_designation'] ? ' (' . $application['contact_designation'] . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['contact_person'], ENT_QUOTES, 'UTF-8'); ?><?php echo $application['contact_designation'] ? ' (' . htmlspecialchars($application['contact_designation'], ENT_QUOTES, 'UTF-8') . ')' : ''; ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Telephone / Mobile</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>E-Mail Id</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Address of site to be audited</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['site_address'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['site_address'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Office address</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['office_address'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['office_address'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Web-site</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>GSTIN Number</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['gstin'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['gstin'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                                <h4 class="app-section-title">2. Company Structure</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Legal status</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['legal_status'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Branch offices / group companies</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['branch_offices'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['branch_offices'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Branch details</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['branch_details'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['branch_details'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Legal obligations applicable</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_obligations_applicable'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['legal_obligations_applicable'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Legal obligations details</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['legal_obligations'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['legal_obligations'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                                <h4 class="app-section-title">3. Certification Requirement</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Type of certification requested</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['certification_type'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['certification_type'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Standard applied for</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['standard_applied'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['standard_applied'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Accreditation requirement</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['accreditation_requirement'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['accreditation_requirement'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Scope / Activity to be certified</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['scope_requirement'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['scope_requirement'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                                <h4 class="app-section-title">4. Multisite Information</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Multisite</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['multisite'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Functions performed centrally</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite_functions'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['multisite_functions'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Sites to be covered</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['multisite_sites'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['multisite_sites'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                                <h4 class="app-section-title">5. Employee Structure</h4>
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
                                    <table class="table table-bordered table-condensed app-detail-table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="vertical-align: middle; min-width: 180px;">Department</th>
                                                <th colspan="2" style="text-align:center;">General shift</th>
                                                <th colspan="2" style="text-align:center;">Shift I</th>
                                                <th colspan="2" style="text-align:center;">Shift II</th>
                                                <th colspan="2" style="text-align:center;">Shift III</th>
                                            </tr>
                                            <tr>
                                                <th style="text-align:center;">Permanent</th>
                                                <th style="text-align:center;">Contract</th>
                                                <th style="text-align:center;">Per.</th>
                                                <th style="text-align:center;">Contract</th>
                                                <th style="text-align:center;">Per.</th>
                                                <th style="text-align:center;">Contract</th>
                                                <th style="text-align:center;">Per.</th>
                                                <th style="text-align:center;">Contract</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Top management / Administrative staff</td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Management and Supervisory staff</td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Operators</td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['gs_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['gs_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s1_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s1_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s2_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s2_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s3_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Operators']['s3_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Helpers</td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['gs_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['gs_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s1_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s1_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s2_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s2_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s3_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Helpers']['s3_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Others (if any)</td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['gs_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['gs_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s1_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s1_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s2_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s2_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s3_perm'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($empMatrix['Others (if any)']['s3_cont'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <table class="table table-bordered table-condensed app-detail-table">
                                    <tbody>
                                        <tr>
                                            <th>Onsite installation / temporary sites</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['onsite_installation'] . ($application['onsite_details'] ? ' - ' . $application['onsite_details'] : ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['onsite_installation'], ENT_QUOTES, 'UTF-8'); ?><?php echo $application['onsite_details'] ? ' - ' . nl2br(htmlspecialchars($application['onsite_details'], ENT_QUOTES, 'UTF-8')) : ''; ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Part time personnel</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['part_time_personnel'] . ($application['part_time_count'] ? ' (' . $application['part_time_count'] . ')' : ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['part_time_personnel'], ENT_QUOTES, 'UTF-8'); ?><?php echo $application['part_time_count'] ? ' (' . htmlspecialchars($application['part_time_count'], ENT_QUOTES, 'UTF-8') . ')' : ''; ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Employees excluded from audit scope</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['employees_excluded'] . ($application['employees_excluded_details'] ? ' - ' . $application['employees_excluded_details'] : ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['employees_excluded'], ENT_QUOTES, 'UTF-8'); ?><?php echo $application['employees_excluded_details'] ? ' - ' . nl2br(htmlspecialchars($application['employees_excluded_details'], ENT_QUOTES, 'UTF-8')) : ''; ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h4 class="app-section-title">6. Describe your organisation</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Organisation type</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['org_type'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['org_type'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Major customers</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['major_customers'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['major_customers'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Main products</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['main_products'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['main_products'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Main raw materials</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['raw_materials'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['raw_materials'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Operations involved</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['operations'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['operations'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>External processes</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['external_processes'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['external_processes'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Design responsibility</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['design_responsibility'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['design_responsibility'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                                <h4 class="app-section-title">7. Other information</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                <tbody>
                                    <tr>
                                        <th>Other certificate(s)</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['other_certificates'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['other_certificates'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Date of system implementation</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['system_implementation_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['system_implementation_date'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Proposed month-year for audit</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['proposed_audit_month'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['proposed_audit_month'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                    <tr>
                                        <th>Consultancy services taken</th>
                                        <td class="app-field" data-copy="<?php echo htmlspecialchars($application['consultancy_services_taken'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['consultancy_services_taken'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                    </tr>
                                        <tr>
                                            <th>Consultancy details</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['consultancy_details'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo nl2br(htmlspecialchars($application['consultancy_details'], ENT_QUOTES, 'UTF-8')); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Supporting attachment(s)</th>
                                            <td>
                                                <?php if (!empty($supportAttachments)) { ?>
                                                    <div style="display:flex; flex-direction:column; gap:6px;">
                                                        <?php foreach ($supportAttachments as $supportAttachment) { ?>
                                                            <a href="<?php echo htmlspecialchars($supportAttachment['path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:7px; color:#2563eb; font-weight:600; text-decoration:none;">
                                                                <i class="fa fa-paperclip"></i>
                                                                <span><?php echo htmlspecialchars($supportAttachment['label'] . ': ' . $supportAttachment['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                <?php } else { ?>
                                                    <span style="color:#9ca3af; font-size:11px;">No supporting attachment uploaded.</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                            </table>

                                <h4 class="app-section-title">Declaration and Signature</h4>
                                <table class="table table-bordered table-condensed app-detail-table">
                                    <tbody>
                                        <tr>
                                            <th style="width: 40%;">Note</th>
                                            <td>
                                                <ul style="padding-left: 18px; margin: 0; font-size: 11px; color: #4b5563;">
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
                                                    <img src="<?php echo $imgSrc; ?>" alt="Signature" style="max-width: 220px; max-height: 110px; border: 1px solid #e5e7eb; padding: 3px; background: #ffffff;">
                                                <?php } else { ?>
                                                    <span style="color:#9ca3af; font-size:11px;">No signature uploaded.</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_name'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['sign_name'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Designation</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_designation'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['sign_designation'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Date</th>
                                            <td class="app-field" data-copy="<?php echo htmlspecialchars($application['sign_date'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($application['sign_date'], ENT_QUOTES, 'UTF-8'); ?><button type="button" class="copy-btn" title="Copy"><i class="fa fa-clone"></i></button></td>
                                        </tr>
                                        <tr>
                                            <th>Declaration</th>
                                            <td><?php echo !empty($application['declaration_confirmed']) ? 'I hereby declaration confirmed by applicant.' : 'Not confirmed on submission.'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                        </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php if ($application) { ?>
<style>
    .edit-modal .modal-dialog {
        width: 92vw;
        max-width: 1220px;
    }
    .edit-modal .modal-body {
        max-height: 72vh;
        overflow-y: auto;
        background: #ffffff;
        padding: 22px 24px;
    }
    .edit-modal .section-block {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 18px 10px;
        margin-bottom: 16px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
    }
    .edit-modal .section-header {
        font-weight: 700;
        font-size: 13.5px;
        color: #111827;
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .edit-modal .section-header:before {
        content: '';
        width: 6px;
        height: 18px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        display: inline-block;
    }
    .edit-modal .form-group label {
        font-weight: 600;
        font-size: 12.5px;
        color: #374151;
    }
    .edit-modal .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 16px 20px;
    }
    .edit-modal textarea.form-control {
        min-height: 70px;
    }
</style>

<!-- Edit Application Modal -->
<div class="modal fade edit-modal" id="editApplicationModal" tabindex="-1" role="dialog" aria-labelledby="editApplicationModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="applicationReceivedView.php?id=<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="id" value="<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="action" value="update_app">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="editApplicationModalLabel">Edit Application</h4>
        </div>
        <div class="modal-body">
          <div class="section-block">
            <div class="section-header">1. Organisation Details</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Organization name *</label>
              <input type="text" name="org_name" class="form-control" required value="<?php echo htmlspecialchars($application['org_name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Contact person *</label>
              <input type="text" name="contact_person" class="form-control" required value="<?php echo htmlspecialchars($application['contact_person'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Designation</label>
              <input type="text" name="contact_designation" class="form-control" value="<?php echo htmlspecialchars($application['contact_designation'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Telephone / Mobile *</label>
              <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($application['phone'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>E-Mail Id *</label>
              <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($application['email'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Web-site</label>
              <input type="text" name="website" class="form-control" value="<?php echo htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>GSTIN Number</label>
              <input type="text" name="gstin" class="form-control" value="<?php echo htmlspecialchars($application['gstin'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Legal status</label>
              <input type="text" name="legal_status" class="form-control" value="<?php echo htmlspecialchars($application['legal_status'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Branch offices / group companies</label>
              <input type="text" name="branch_offices" class="form-control" value="<?php echo htmlspecialchars($application['branch_offices'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Branch details</label>
              <textarea name="branch_details" class="form-control" rows="2"><?php echo htmlspecialchars($application['branch_details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Legal obligations applicable</label>
              <input type="text" name="legal_obligations_applicable" class="form-control" value="<?php echo htmlspecialchars($application['legal_obligations_applicable'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Legal obligations details</label>
              <textarea name="legal_obligations" class="form-control" rows="2"><?php echo htmlspecialchars($application['legal_obligations'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">2. Addresses</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Address of site to be audited *</label>
              <textarea name="site_address" class="form-control" rows="2" required><?php echo htmlspecialchars($application['site_address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Office address *</label>
              <textarea name="office_address" class="form-control" rows="2" required><?php echo htmlspecialchars($application['office_address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">3. Certification</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Type of certification requested</label>
              <textarea name="certification_type" class="form-control" rows="2"><?php echo htmlspecialchars($application['certification_type'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Standards applied for</label>
              <textarea name="standard_applied" class="form-control" rows="2"><?php echo htmlspecialchars($application['standard_applied'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Accreditation requirement</label>
              <textarea name="accreditation_requirement" class="form-control" rows="2"><?php echo htmlspecialchars($application['accreditation_requirement'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Scope / Activity to be certified</label>
              <textarea name="scope_requirement" class="form-control" rows="2"><?php echo htmlspecialchars($application['scope_requirement'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">4. Multisite</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Multisite (Yes/No)</label>
              <input type="text" name="multisite" class="form-control" value="<?php echo htmlspecialchars($application['multisite'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Functions performed centrally</label>
              <textarea name="multisite_functions" class="form-control" rows="2"><?php echo htmlspecialchars($application['multisite_functions'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Sites to be covered</label>
              <textarea name="multisite_sites" class="form-control" rows="2"><?php echo htmlspecialchars($application['multisite_sites'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">5. Employees / Operations</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Employee structure</label>
              <textarea name="employee_structure" class="form-control" rows="2"><?php echo htmlspecialchars($application['employee_structure'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Onsite installation work (Yes/No)</label>
              <input type="text" name="onsite_installation" class="form-control" value="<?php echo htmlspecialchars($application['onsite_installation'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Onsite details</label>
              <textarea name="onsite_details" class="form-control" rows="2"><?php echo htmlspecialchars($application['onsite_details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Part time personnel (Yes/No)</label>
              <input type="text" name="part_time_personnel" class="form-control" value="<?php echo htmlspecialchars($application['part_time_personnel'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>No. of part time personnel</label>
              <input type="text" name="part_time_count" class="form-control" value="<?php echo htmlspecialchars($application['part_time_count'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Employees excluded from audit scope</label>
              <input type="text" name="employees_excluded" class="form-control" value="<?php echo htmlspecialchars($application['employees_excluded'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Employees excluded details</label>
              <textarea name="employees_excluded_details" class="form-control" rows="2"><?php echo htmlspecialchars($application['employees_excluded_details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">6. Consultancy &amp; Company</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Consultancy services taken (Yes/No)</label>
              <input type="text" name="consultancy_services_taken" class="form-control" value="<?php echo htmlspecialchars($application['consultancy_services_taken'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Consultancy details</label>
              <textarea name="consultancy_details" class="form-control" rows="2"><?php echo htmlspecialchars($application['consultancy_details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Organisation type</label>
              <input type="text" name="org_type" class="form-control" value="<?php echo htmlspecialchars($application['org_type'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Major customers</label>
              <textarea name="major_customers" class="form-control" rows="2"><?php echo htmlspecialchars($application['major_customers'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Main products</label>
              <textarea name="main_products" class="form-control" rows="2"><?php echo htmlspecialchars($application['main_products'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Main raw materials used / processed</label>
              <textarea name="raw_materials" class="form-control" rows="2"><?php echo htmlspecialchars($application['raw_materials'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Operations involved</label>
              <textarea name="operations" class="form-control" rows="2"><?php echo htmlspecialchars($application['operations'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>External processes</label>
              <textarea name="external_processes" class="form-control" rows="2"><?php echo htmlspecialchars($application['external_processes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Design responsibility</label>
              <input type="text" name="design_responsibility" class="form-control" value="<?php echo htmlspecialchars($application['design_responsibility'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            </div>
          </div>

          <div class="section-block">
            <div class="section-header">7. Other details</div>
            <div class="grid-2">
            <div class="form-group">
              <label>Other certificates</label>
              <textarea name="other_certificates" class="form-control" rows="2"><?php echo htmlspecialchars($application['other_certificates'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="form-group">
              <label>Date of system implementation</label>
              <input type="text" name="system_implementation_date" class="form-control" value="<?php echo htmlspecialchars($application['system_implementation_date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
              <label>Proposed month-year for audit</label>
              <input type="text" name="proposed_audit_month" class="form-control" value="<?php echo htmlspecialchars($application['proposed_audit_month'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    .delete-modal .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.25);
    }
    .delete-modal .modal-header {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: #fff;
        border: none;
        padding: 18px 20px 14px;
    }
    .delete-modal .modal-title {
        font-weight: 700;
        font-size: 18px;
        margin: 0;
    }
    .delete-modal .modal-header .close {
        color: #fff;
        opacity: 0.9;
        text-shadow: none;
    }
    .delete-modal .modal-body {
        padding: 18px 22px 12px;
        background: #fff7f7;
    }
    .delete-modal .danger-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-right: 12px;
    }
    .delete-modal .danger-title {
        font-size: 16px;
        font-weight: 700;
        color: #7f1d1d;
        margin-bottom: 6px;
    }
    .delete-modal .danger-text {
        font-size: 13px;
        color: #4b5563;
        margin: 0 0 8px 0;
    }
    .delete-modal .danger-list {
        margin: 0 0 6px 0;
        padding-left: 18px;
        color: #6b7280;
        font-size: 12.5px;
    }
    .delete-modal .modal-footer {
        border-top: 1px solid #f3f4f6;
        background: #ffffff;
        padding: 12px 18px 14px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .delete-modal .btn-cancel {
        border: 1px solid #d1d5db;
        color: #4b5563;
        background: #fff;
        border-radius: 999px;
        padding: 8px 16px;
        font-weight: 600;
    }
    .delete-modal .btn-delete {
        border: none;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        border-radius: 999px;
        padding: 9px 18px;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.35);
    }
    .delete-modal .btn-delete:hover {
        filter: brightness(0.96);
    }
</style>

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-modal" id="deleteApplicationModal" tabindex="-1" role="dialog" aria-labelledby="deleteApplicationModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" action="applicationReceivedView.php?id=<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="id" value="<?php echo (int)$application['id']; ?>">
        <input type="hidden" name="action" value="delete_app">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="deleteApplicationModalLabel">Delete Application</h4>
        </div>
        <div class="modal-body">
          <div style="display:flex; align-items:flex-start; gap:12px;">
            <div class="danger-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div>
              <div class="danger-title">This action will permanently remove the application.</div>
              <p class="danger-text">Are you sure you want to delete this application? This cannot be undone.</p>
              <ul class="danger-list">
                <li>All application details will be lost.</li>
                <li>No further access to this record after deletion.</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger btn-delete">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php } ?>
<script>
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
        setTimeout(function () { btn.classList.remove('copied'); }, 900);
    });
})();
</script>

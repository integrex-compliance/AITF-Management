<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

try {
    $db->exec("ALTER TABLE information_updation_forms ADD COLUMN aitf_review TEXT NULL");
} catch (Exception $e) {
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$submission = null;
$error = '';
$success = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_app') {
    $isTopMgmt = (isset($_SESSION['role']) && trim($_SESSION['role']) === 'Top Mgmt') || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role());
    if (!$isTopMgmt) {
        $error = 'You are not allowed to perform this action. Only Top Management can delete the form.';
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM information_updation_forms WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                header('Location: applicationReceived.php?deleted=1');
                exit;
            } catch (Exception $e) {
                $error = "Error deleting submission: " . $e->getMessage();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_aitf_review') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $review = trim((string)($_POST['aitf_review'] ?? ''));
    if ($id > 0) {
        try {
            $stmt = $db->prepare("UPDATE information_updation_forms SET aitf_review = :review WHERE id = :id");
            $stmt->bindParam(':review', $review, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $success = 'AITF review saved successfully.';
        } catch (Exception $e) {
            $error = "Error saving AITF review: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_auditor_review') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $review = trim((string)($_POST['auditor_review'] ?? ''));
    if ($id > 0) {
        try {
            $stmt = $db->prepare("UPDATE information_updation_forms SET auditor_review = :review WHERE id = :id");
            $stmt->bindParam(':review', $review, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $success = 'Auditor review saved successfully.';
        } catch (Exception $e) {
            $error = "Error saving auditor review: " . $e->getMessage();
        }
    }
}

// Fetch submission details
if ($id > 0 && isset($db)) {
    try {
        $stmt = $db->prepare("SELECT * FROM information_updation_forms WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Mark as read
        if ($submission && isset($submission['read_flag']) && (int)$submission['read_flag'] === 0) {
            $markRead = $db->prepare("UPDATE information_updation_forms SET read_flag = 1 WHERE id = :id");
            $markRead->bindParam(':id', $id, PDO::PARAM_INT);
            $markRead->execute();
        }
    } catch (Exception $e) {
        $error = "Error fetching submission: " . $e->getMessage();
    }
}

$pageIdentifier = "Application Received";
?>

<div id="wrapper" class="active">
<?php 
include 'header.php';
$isAuditorView = isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor';
?>
<?php if ($isAuditorView): ?>
<style>
    /* Hide the left sidebar menu completely for auditors */
    #sidebar-wrapper { display: none !important; }
    #wrapper, #wrapper.active { padding-left: 0 !important; }
    
    /* Disable the edit and delete buttons for auditors */
    .fa-trash, .btn-danger, [onclick*="delete"], 
    .fa-edit, .btn-primary[data-target*="edit"], [data-target*="edit"] { 
        pointer-events: none !important; 
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }
    
    /* Optional: add a tooltip-like effect for disabled items */
    .fa-trash:hover::after, .btn-danger:hover::after, [onclick*="delete"]:hover::after,
    .fa-edit:hover::after, [data-target*="edit"]:hover::after {
        content: "Action not allowed";
        position: absolute;
        background: #333;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-top: -30px;
        white-space: nowrap;
        z-index: 1000;
    }
</style>
<?php endif; ?>
<style>
* { box-sizing: border-box; }
.minimal-wrapper {
    max-width: 800px;
    margin: 40px auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #333;
    padding: 0 20px;
}
.minimal-header {
    background: #ffffff;
    padding: 25px 30px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}
.minimal-title {
    position: relative;
    z-index: 1;
}
.minimal-title h2 {
    margin: 0 0 12px 0;
    font-size: 24px;
    font-weight: 700;
    color: #2563eb;
    display: flex;
    align-items: center;
    gap: 10px;
}
.minimal-meta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f3f4f6;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    color: #4b5563;
}
.minimal-meta i {
    font-size: 14px;
    color: #6b7280;
}
.minimal-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}
.btn-header {
    padding: 10px 20px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    color: #374151;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-header:hover {
    background: #f9fafb;
    color: #111827;
    border-color: #9ca3af;
    text-decoration: none;
}
.btn-header-primary {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
.btn-header-primary:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
    transform: translateY(-1px);
}
.btn-outline {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: #fff;
    color: #333;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-block;
}
.btn-outline:hover {
    background: #f5f5f5;
    border-color: #ccc;
    color: #000;
    text-decoration: none;
}
.btn-primary {
    background: #000;
    color: #fff;
    border: 1px solid #000;
}
.btn-primary:hover {
    background: #333;
    color: #fff;
    border-color: #333;
}
.minimal-section {
    margin-bottom: 40px;
}
.minimal-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #000;
    margin: 0 0 15px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.minimal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 600px) {
    .minimal-grid { grid-template-columns: 1fr; }
    .minimal-header { flex-direction: column; align-items: flex-start; gap: 15px; padding: 20px; }
}
.minimal-field {
    margin-bottom: 5px;
}
.minimal-label {
    display: block;
    font-size: 12px;
    color: #777;
    margin-bottom: 4px;
}
.minimal-value {
    font-size: 15px;
    color: #111;
    font-weight: 500;
}
.minimal-change-card {
    border: 1px solid #eaeaea;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 12px;
}
.change-yes { border-left: 3px solid #000; background: #fafafa; }
.change-no { opacity: 0.6; }
.change-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
}
.change-badge {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 3px;
    background: #eee;
    text-transform: uppercase;
}
.change-badge.active { background: #000; color: #fff; }
.change-desc {
    font-size: 14px;
    color: #444;
    line-height: 1.5;
    margin-top: 8px;
}
.minimal-signature img {
    max-width: 200px;
    border: 1px solid #eee;
    padding: 10px;
}
.minimal-review textarea {
    width: 100%;
    min-height: 120px;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
    margin-bottom: 10px;
    resize: vertical;
}
.minimal-review textarea:focus {
    outline: none;
    border-color: #000;
}
.minimal-help {
    font-size: 12px;
    color: #777;
    margin-bottom: 10px;
}
</style>

<div id="page-content-wrapper">
    <div class="page-content inset">
        <div class="minimal-wrapper">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; border-radius: 4px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <?php if ($submission): ?>
                    <div class="minimal-header">
                        <div class="minimal-title">
                            <h2><i class="fa fa-refresh"></i> Information Updation Form Submission</h2>
                            <div class="minimal-meta">
                                <i class="fa fa-calendar"></i>
                                <span><?php 
                                    try {
                                        $dt = new DateTimeImmutable($submission['created_at'], new DateTimeZone('Asia/Kolkata'));
                                        echo $dt->format('d M Y, h:i A');
                                    } catch (Exception $e) {
                                        echo htmlspecialchars($submission['created_at'], ENT_QUOTES, 'UTF-8');
                                    }
                                ?></span>
                            </div>
                        </div>
                        <div class="minimal-actions">
                            <a href="<?php echo $isAuditorView ? 'auditorDashboard.php' : 'applicationReceived.php'; ?>" class="btn-header">Back</a>
                            <a href="info_updation_pdf.php?id=<?php echo (int)$submission['id']; ?>" target="_blank" class="btn-header btn-header-primary">Download PDF</a>
                        </div>
                    </div>
                    
                    
                    <?php if (!empty($submission['admin_assign_message'])) { ?>
                        <div class="minimal-section">
                            <h3 class="minimal-section-title" style="color: #1e3a8a;">Message from Admin</h3>
                            <div style="padding:16px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #3b82f6; border-radius: 6px;">
                                <p style="margin:0; font-size: 14px; color: #1e3a8a; font-style: italic;">
                                    <?php echo nl2br(htmlspecialchars($submission['admin_assign_message'], ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Organization Details</h3>
                        <div class="minimal-grid">
                            <div class="minimal-field">
                                <span class="minimal-label">Organization Name</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['org_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Certificate Number</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['certificate_number'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Contact Person</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['contact_person'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Designation</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['contact_designation'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Phone</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Email</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Reported Changes</h3>
                        <?php
                        $changes = [
                            ['label' => 'Company Name', 'change' => 'company_name_change', 'details' => 'company_name_details'],
                            ['label' => 'Organization Ownership', 'change' => 'ownership_change', 'details' => 'ownership_details'],
                            ['label' => 'Legal Status', 'change' => 'legal_status_change', 'details' => 'legal_status_details'],
                            ['label' => 'Address/Location', 'change' => 'address_change', 'details' => 'address_details'],
                            ['label' => 'Site Addition', 'change' => 'site_addition_change', 'details' => 'site_addition_details'],
                            ['label' => 'Office/Communication Address', 'change' => 'office_address_change', 'details' => 'office_address_details'],
                            ['label' => 'Telephone/Mobile', 'change' => 'phone_change', 'details' => 'phone_change_details'],
                            ['label' => 'Email/Website', 'change' => 'email_change', 'details' => 'email_change_details'],
                            ['label' => 'Contact Person', 'change' => 'contact_person_change', 'details' => 'contact_person_change_details'],
                            ['label' => 'Certified Activity/Scope', 'change' => 'activity_change', 'details' => 'activity_details'],
                            ['label' => 'Number of Employees', 'change' => 'employee_change', 'details' => 'employee_details']
                        ];
                        
                        foreach ($changes as $change) {
                            $isChanged = ($submission[$change['change']] === 'Yes');
                            $cardClass = $isChanged ? 'change-yes' : 'change-no';
                            $badgeClass = $isChanged ? 'active' : '';
                            $statusText = $isChanged ? 'Changed' : 'No Change';
                            ?>
                            <div class="minimal-change-card <?php echo $cardClass; ?>">
                                <div class="change-header">
                                    <span><?php echo $change['label']; ?></span>
                                    <span class="change-badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                                <?php if ($isChanged): ?>
                                <div class="change-desc">
                                    <?php echo nl2br(htmlspecialchars($submission[$change['details']], ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Consultancy Services</h3>
                        <?php $isConsultancy = ($submission['consultancy_services'] === 'Yes'); ?>
                        <div class="minimal-change-card <?php echo $isConsultancy ? 'change-yes' : 'change-no'; ?>">
                            <div class="change-header">
                                <span>Consultancy services or in-house training performed</span>
                                <span class="change-badge <?php echo $isConsultancy ? 'active' : ''; ?>"><?php echo $isConsultancy ? 'Yes' : 'No'; ?></span>
                            </div>
                            <?php if ($isConsultancy): ?>
                            <div class="change-desc">
                                <?php echo nl2br(htmlspecialchars($submission['consultancy_details'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Declaration</h3>
                        <div class="minimal-grid" style="margin-bottom: 20px;">
                            <div class="minimal-field">
                                <span class="minimal-label">Name</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['sign_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Designation</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['sign_designation'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Date</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['sign_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($submission['signature_image'])): ?>
                            <div class="minimal-field">
                                <span class="minimal-label">Signature</span>
                                <div class="minimal-signature" style="margin-top: 8px;">
                                    <?php 
                                    $signature_path = htmlspecialchars($submission['signature_image'], ENT_QUOTES, 'UTF-8');
                                    $file_extension = strtolower(pathinfo($signature_path, PATHINFO_EXTENSION));
                                    
                                    if ($file_extension === 'pdf'): ?>
                                        <a href="<?php echo $signature_path; ?>" target="_blank" class="btn-outline">View Signature PDF</a>
                                    <?php else: ?>
                                        <img src="<?php echo $signature_path; ?>" alt="Signature">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="minimal-section">
                        <h3 class="minimal-section-title">AITF Review (For PDF)</h3>
                        <div class="minimal-review">
                            <form method="post">
                                <input type="hidden" name="action" value="save_aitf_review">
                                <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
                                <textarea name="aitf_review" placeholder="Write AITF review here..."><?php echo htmlspecialchars($submission['aitf_review'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <div class="minimal-help">This text appears inside the "FOR AITF REVIEW" box in the downloaded PDF.</div>
                                <button type="submit" class="btn-outline btn-primary">Save AITF Review</button>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($submission['auditor_review']) || Utitlity::checkForAuditor() || Utitlity::checkForUserMainAdmin()) { ?>
                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Auditor Review</h3>
                        <div class="minimal-review">
                            <?php if (Utitlity::checkForAuditor() || Utitlity::checkForUserMainAdmin()) { ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="save_auditor_review">
                                    <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
                                    <textarea name="auditor_review" placeholder="Write Auditor review here..."><?php echo htmlspecialchars($submission['auditor_review'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <button type="submit" class="btn-outline btn-primary">Save Auditor Review</button>
                                </form>
                            <?php } else { ?>
                                <p style="margin:0; font-size: 14px;"><?php echo nl2br(htmlspecialchars($submission['auditor_review'] ?? 'No review provided yet.', ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                <?php else: ?>
                    <div style="padding: 20px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; color: #92400e; margin-bottom: 20px;">
                        Not Found: The requested information updation form submission could not be found.<br><br>
                        <a href="<?php echo $isAuditorView ? 'auditorDashboard.php' : 'applicationReceived.php'; ?>" class="btn-outline">Back to List</a>
                    </div>
                <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("active");
        $(".container, .container-fluid").toggleClass("active");
    });
</script>

</div>

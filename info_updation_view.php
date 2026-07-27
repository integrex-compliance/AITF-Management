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
            $error = "Error saving review: " . $e->getMessage();
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
<?php include 'header.php'; ?>

<style>
* {
    box-sizing: border-box;
}

.info-update-view-wrapper {
    max-width: 1200px;
    margin: 20px auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 0 15px;
}

/* Top Action Bar - Sticky */
.top-action-bar {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    padding: 18px 25px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    position: sticky;
    top: 10px;
    z-index: 100;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.action-bar-title {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.action-bar-title i {
    font-size: 22px;
    color: #60a5fa;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.info-update-view-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
    padding: 0;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.info-update-view-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    color: #ffffff;
    padding: 30px 35px;
    position: relative;
    overflow: hidden;
}

.info-update-view-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.info-update-view-header h3 {
    margin: 0 0 15px 0;
    font-size: 28px;
    font-weight: 700;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.info-update-view-meta {
    display: flex;
    gap: 25px;
    font-size: 14px;
    opacity: 0.95;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.info-update-view-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    padding: 6px 14px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.info-update-view-meta i {
    font-size: 16px;
}

.info-update-view-section {
    border: none;
    padding: 30px 35px;
    margin: 0;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
    transition: all 0.3s ease;
}

.info-update-view-section:hover {
    background: #f8fafc;
}

.info-update-view-section:last-child {
    border-bottom: none;
}

.info-update-view-section h4 {
    margin: 0 0 25px 0;
    color: #1e293b;
    font-size: 20px;
    font-weight: 700;
    border-bottom: 3px solid #3b82f6;
    padding-bottom: 12px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.info-update-view-section h4 i {
    color: #3b82f6;
    font-size: 22px;
}

.info-update-view-field {
    margin-bottom: 18px;
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 15px;
    align-items: start;
    padding: 12px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.info-update-view-field:hover {
    background: #f1f5f9;
}

.info-update-view-field label {
    font-weight: 600;
    color: #475569;
    font-size: 14px;
}

.info-update-view-field span {
    color: #1e293b;
    font-size: 15px;
    line-height: 1.6;
}

.info-update-view-change {
    margin-bottom: 18px;
    padding: 18px;
    border-radius: 12px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.info-update-view-change:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.change-indicator {
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
}

.change-indicator.yes {
    color: #0f766e;
}

.change-indicator.yes .fa {
    color: #14b8a6;
    font-size: 20px;
}

.change-indicator.no {
    color: #64748b;
}

.change-indicator.no .fa {
    color: #94a3b8;
    font-size: 20px;
}

.change-details {
    padding: 15px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    color: #1e293b;
    line-height: 1.7;
    font-size: 14px;
}

.btn-back {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
    min-width: 140px;
    justify-content: center;
}

.btn-back:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(100, 116, 139, 0.4);
}

.btn-download {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    min-width: 160px;
    justify-content: center;
}

.btn-download:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    min-width: 170px;
    justify-content: center;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.btn-save {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-save:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.aitf-review-textarea {
    width: 100%;
    min-height: 150px;
    border: 2px solid #cbd5e1;
    border-radius: 12px;
    padding: 15px;
    font-family: "Times New Roman", Times, serif;
    font-size: 15px;
    line-height: 1.6;
    color: #1e293b;
    background: #ffffff;
    transition: all 0.3s ease;
    resize: vertical;
}

.aitf-review-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.aitf-review-help {
    font-family: "Times New Roman", Times, serif;
    font-size: 13px;
    color: #64748b;
    font-style: italic;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.aitf-review-help::before {
    content: "ℹ️";
    font-style: normal;
}

.signature-container {
    border: 2px solid #d1fae5;
    border-radius: 12px;
    padding: 15px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    display: inline-block;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
}

.signature-container img {
    max-width: 300px;
    max-height: 150px;
    display: block;
    border-radius: 6px;
}

.pdf-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #fbbf24;
    border-radius: 10px;
    color: #92400e;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pdf-link:hover {
    background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
    text-decoration: none;
    color: #78350f;
}

.pdf-link i {
    font-size: 24px;
}

@media (max-width: 768px) {
    .info-update-view-wrapper {
        margin: 10px auto;
        padding: 0 10px;
    }
    
    .top-action-bar {
        flex-direction: column;
        align-items: stretch;
        padding: 15px;
        position: relative;
        top: 0;
    }
    
    .action-bar-title {
        font-size: 16px;
        justify-content: center;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .action-buttons .btn-back,
    .action-buttons .btn-download,
    .action-buttons .btn-delete {
        width: 100%;
        min-width: 0;
    }
    
    .info-update-view-card {
        border-radius: 12px;
    }
    
    .info-update-view-header {
        padding: 20px;
    }
    
    .info-update-view-header h3 {
        font-size: 22px;
    }
    
    .info-update-view-section {
        padding: 20px;
    }
    
    .info-update-view-field {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 10px;
    }
    
    .info-update-view-field label {
        min-width: auto;
    }
    
    .info-update-view-meta {
        gap: 10px;
    }
}
</style>

<div id="page-content-wrapper">
    <div class="page-content inset">
        <div class="info-update-view-wrapper">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <strong><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                <?php endif; ?>
                
                <?php if ($submission): ?>
                    <!-- Top Action Bar -->
                    <div class="top-action-bar">
                        <h2 class="action-bar-title">
                            <i class="fa fa-file-text"></i>
                            Information Updation Form
                        </h2>
                        <div class="action-buttons">
                            <a href="applicationReceived.php" class="btn-back">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="info_updation_pdf.php?id=<?php echo (int)$submission['id']; ?>" target="_blank" class="btn-download">
                                <i class="fa fa-file-pdf-o"></i> Download PDF
                            </a>
                            <form method="post" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this submission? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete_app">
                                <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fa fa-trash"></i> Delete Submission
                                </button>
                            </form>
                        </div>
                    </div>
                
                    <div class="info-update-view-card">
                        <div class="info-update-view-header">
                            <h3><i class="fa fa-refresh"></i> Information Updation Form Submission</h3>
                            <div class="info-update-view-meta">
                                <span>
                                    <i class="fa fa-calendar"></i>
                                    <?php 
                                    try {
                                        $dt = new DateTimeImmutable($submission['created_at'], new DateTimeZone('Asia/Kolkata'));
                                        echo $dt->format('d M Y, h:i A');
                                    } catch (Exception $e) {
                                        echo htmlspecialchars($submission['created_at'], ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </span>
                                <span><i class="fa fa-hashtag"></i> ID: <?php echo (int)$submission['id']; ?></span>
                            </div>
                        </div>
                        
                        <!-- Organization Details Section -->
                        <div class="info-update-view-section">
                            <h4><i class="fa fa-building"></i> Organization Details</h4>
                            <div class="info-update-view-field">
                                <label>Organization Name:</label>
                                <span><?php echo htmlspecialchars($submission['org_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Certificate Number:</label>
                                <span><?php echo htmlspecialchars($submission['certificate_number'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Contact Person:</label>
                                <span><?php echo htmlspecialchars($submission['contact_person'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Designation:</label>
                                <span><?php echo htmlspecialchars($submission['contact_designation'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Phone:</label>
                                <span><?php echo htmlspecialchars($submission['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($submission['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Changes Section -->
                        <div class="info-update-view-section">
                            <h4><i class="fa fa-edit"></i> Reported Changes</h4>
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
                                if ($submission[$change['change']] === 'Yes') {
                                    ?>
                                    <div class="info-update-view-change">
                                        <div class="change-indicator yes">
                                            <i class="fa fa-check-circle"></i> <?php echo $change['label']; ?> - Changed
                                        </div>
                                        <div class="change-details">
                                            <?php echo nl2br(htmlspecialchars($submission[$change['details']], ENT_QUOTES, 'UTF-8')); ?>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="info-update-view-change">
                                        <div class="change-indicator no">
                                            <i class="fa fa-times-circle"></i> <?php echo $change['label']; ?> - No Change
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <!-- Consultancy Services Section -->
                        <div class="info-update-view-section">
                            <h4><i class="fa fa-question-circle"></i> Consultancy Services</h4>
                            <?php if ($submission['consultancy_services'] === 'Yes'): ?>
                                <div class="info-update-view-change">
                                    <div class="change-indicator yes">
                                        <i class="fa fa-check-circle"></i> Consultancy services or in-house training performed
                                    </div>
                                    <div class="change-details">
                                        <?php echo nl2br(htmlspecialchars($submission['consultancy_details'], ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-update-view-change">
                                    <div class="change-indicator no">
                                        <i class="fa fa-times-circle"></i> No consultancy services or in-house training performed
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Signature Section -->
                        <div class="info-update-view-section">
                            <h4><i class="fa fa-pencil"></i> Signature & Declaration</h4>
                            <div class="info-update-view-field">
                                <label>Name:</label>
                                <span><?php echo htmlspecialchars($submission['sign_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Designation:</label>
                                <span><?php echo htmlspecialchars($submission['sign_designation'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="info-update-view-field">
                                <label>Date:</label>
                                <span><?php echo htmlspecialchars($submission['sign_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <?php if (!empty($submission['signature_image'])): ?>
                                <div class="info-update-view-field" style="margin-top: 20px;">
                                    <label>Signature:</label>
                                    <div>
                                        <?php 
                                        $signature_path = htmlspecialchars($submission['signature_image'], ENT_QUOTES, 'UTF-8');
                                        $file_extension = strtolower(pathinfo($signature_path, PATHINFO_EXTENSION));
                                        
                                        if ($file_extension === 'pdf'): ?>
                                            <a href="<?php echo $signature_path; ?>" target="_blank" class="pdf-link">
                                                <i class="fa fa-file-pdf-o"></i>
                                                <span>View Signature PDF</span>
                                            </a>
                                        <?php else: ?>
                                            <div class="signature-container">
                                                <img src="<?php echo $signature_path; ?>" alt="Signature">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="info-update-view-section">
                            <h4><i class="fa fa-gavel"></i> FOR AITF REVIEW</h4>
                            <div style="margin-bottom: 12px;">
                                <form method="post" style="margin: 0;">
                                    <input type="hidden" name="action" value="save_aitf_review">
                                    <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
                                    <textarea class="aitf-review-textarea" name="aitf_review" placeholder="Write AITF review here..."><?php echo htmlspecialchars($submission['aitf_review'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <div class="aitf-review-help">This text appears inside the “FOR AITF REVIEW” box in the downloaded PDF.</div>
                                    <div style="margin-top: 12px; text-align: right;">
                                        <button type="submit" class="btn-save">
                                            <i class="fa fa-save"></i> Save Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>Not Found:</strong> The requested information updation form submission could not be found.
                        <br><br>
                        <a href="applicationReceived.php" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
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

<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$submission = null;
$error = '';
$success = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_app') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM nc_closing_forms WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: applicationReceived.php?deleted=1');
            exit;
        } catch (Exception $e) {
            $error = "Error deleting submission: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_assessor_review') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $assessor_accepted = trim((string)($_POST['assessor_accepted'] ?? ''));
    $assessor_comments = trim((string)($_POST['assessor_comments'] ?? ''));
    $assessor_verification_method = implode(", ", $_POST['assessor_verification_method'] ?? []);
    $team_leader_name = trim((string)($_POST['team_leader_name'] ?? ''));
    $team_leader_signature = trim((string)($_POST['team_leader_signature'] ?? ''));
    $team_leader_date = trim((string)($_POST['team_leader_date'] ?? ''));
    $assessor_name = trim((string)($_POST['assessor_name'] ?? ''));
    $assessor_signature = trim((string)($_POST['assessor_signature'] ?? ''));
    $assessor_date = trim((string)($_POST['assessor_date'] ?? ''));
    $follow_up_effectiveness = trim((string)($_POST['follow_up_effectiveness'] ?? ''));
    $verifying_assessor_sign = trim((string)($_POST['verifying_assessor_sign'] ?? ''));
    $verifying_assessor_date = trim((string)($_POST['verifying_assessor_date'] ?? ''));

    if ($assessor_accepted === 'Yes') {
        $assessor_comments = '';
        $assessor_name = '';
        $assessor_signature = '';
        $assessor_date = '';
    } elseif ($assessor_accepted === 'No' || $assessor_accepted === 'Further action') {
        $assessor_verification_method = '';
        $team_leader_name = '';
        $team_leader_signature = '';
        $team_leader_date = '';
    }

    if ($id > 0) {
        try {
            $stmt = $db->prepare("UPDATE nc_closing_forms SET 
                assessor_accepted = :assessor_accepted,
                assessor_comments = :assessor_comments,
                assessor_verification_method = :assessor_verification_method,
                team_leader_name = :team_leader_name,
                team_leader_signature = :team_leader_signature,
                team_leader_date = :team_leader_date,
                assessor_name = :assessor_name,
                assessor_signature = :assessor_signature,
                assessor_date = :assessor_date,
                follow_up_effectiveness = :follow_up_effectiveness,
                verifying_assessor_sign = :verifying_assessor_sign,
                verifying_assessor_date = :verifying_assessor_date
                WHERE id = :id");
            
            $stmt->execute([
                ':assessor_accepted' => $assessor_accepted,
                ':assessor_comments' => $assessor_comments,
                ':assessor_verification_method' => $assessor_verification_method,
                ':team_leader_name' => $team_leader_name,
                ':team_leader_signature' => $team_leader_signature,
                ':team_leader_date' => $team_leader_date,
                ':assessor_name' => $assessor_name,
                ':assessor_signature' => $assessor_signature,
                ':assessor_date' => $assessor_date,
                ':follow_up_effectiveness' => $follow_up_effectiveness,
                ':verifying_assessor_sign' => $verifying_assessor_sign,
                ':verifying_assessor_date' => $verifying_assessor_date,
                ':id' => $id
            ]);
            $success = 'Assessor review saved successfully.';
        } catch (Exception $e) {
            $error = "Error saving review: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_auditor_review') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $review = trim((string)($_POST['auditor_review'] ?? ''));
    if ($id > 0) {
        try {
            $stmt = $db->prepare("UPDATE nc_closing_forms SET auditor_review = :review WHERE id = :id");
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
        $stmt = $db->prepare("SELECT * FROM nc_closing_forms WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Mark as read
        if ($submission && isset($submission['read_flag']) && (int)$submission['read_flag'] === 0) {
            $markRead = $db->prepare("UPDATE nc_closing_forms SET read_flag = 1 WHERE id = :id");
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
    max-width: 900px;
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
    color: #0284c7;
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
}
.btn-header-primary {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
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
}
.btn-primary {
    background: #000;
    color: #fff;
    border: 1px solid #000;
}
.minimal-section {
    margin-bottom: 40px;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}
.minimal-section-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 20px 0;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}
.minimal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.minimal-field {
    margin-bottom: 15px;
}
.minimal-label {
    display: block;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 600;
}
.minimal-value {
    font-size: 15px;
    color: #111827;
    background: #f9fafb;
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #f3f4f6;
}
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 15px;
}
textarea.form-control {
    min-height: 80px;
}
.checkbox-group label, .radio-group label {
    display: inline-block;
    margin-right: 15px;
    font-weight: 500;
    cursor: pointer;
}
.checkbox-group input, .radio-group input {
    margin-right: 5px;
}
</style>

<div id="page-content-wrapper">
    <div class="page-content inset">
        <div class="minimal-wrapper">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                
                <?php if ($submission): ?>
                    <div class="minimal-header">
                        <div class="minimal-title">
                            <h2><i class="fa fa-file-text"></i> NC Closing Form Submission</h2>
                            <div class="minimal-meta">
                                <i class="fa fa-calendar"></i>
                                <span><?php echo htmlspecialchars($submission['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                        <div class="minimal-actions">
                            <a href="<?php echo $isAuditorView ? 'auditorDashboard.php' : 'applicationReceived.php'; ?>" class="btn-header">Back</a>
                            <a href="nc_closing_pdf.php?id=<?php echo (int)$submission['id']; ?>" target="_blank" class="btn-header btn-header-primary">Download PDF</a>
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
                        <h3 class="minimal-section-title">General Information</h3>
                        <div class="minimal-grid">
                            <div class="minimal-field">
                                <span class="minimal-label">Organization Name</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['org_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Organisation Mail ID</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Standard</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['standard'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Date of Audit</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['audit_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">NC Reference No</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['nc_reference_no'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Corrective Action Date</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['corrective_action_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Root Cause Analysis & Corrective Actions</h3>
                        <div class="minimal-field">
                            <span class="minimal-label">Root Cause</span>
                            <div class="minimal-value"><?php echo nl2br(htmlspecialchars($submission['root_cause'], ENT_QUOTES, 'UTF-8')); ?></div>
                        </div>
                        <div class="minimal-field">
                            <span class="minimal-label">Correction</span>
                            <div class="minimal-value"><?php echo nl2br(htmlspecialchars($submission['correction'], ENT_QUOTES, 'UTF-8')); ?></div>
                        </div>
                        <div class="minimal-field">
                            <span class="minimal-label">Corrective Action</span>
                            <div class="minimal-value"><?php echo nl2br(htmlspecialchars($submission['corrective_action'], ENT_QUOTES, 'UTF-8')); ?></div>
                        </div>
                    </div>
                    
                    <div class="minimal-section">
                        <h3 class="minimal-section-title">Person Responsible (Auditee)</h3>
                        <div class="minimal-grid">
                            <div class="minimal-field">
                                <span class="minimal-label">Name</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['sign_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Designation</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['sign_designation'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="minimal-field">
                                <span class="minimal-label">Proposed Completion Date</span>
                                <div class="minimal-value"><?php echo htmlspecialchars($submission['proposed_completion_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($submission['signature_image'])): ?>
                            <div class="minimal-field">
                                <span class="minimal-label">Signature</span>
                                <div>
                                    <?php 
                                    $signature_path = htmlspecialchars($submission['signature_image'], ENT_QUOTES, 'UTF-8');
                                    $file_extension = strtolower(pathinfo($signature_path, PATHINFO_EXTENSION));
                                    if ($file_extension === 'pdf'): ?>
                                        <a href="<?php echo $signature_path; ?>" target="_blank" class="btn-outline">View Signature PDF</a>
                                    <?php else: ?>
                                        <img src="<?php echo $signature_path; ?>" alt="Signature" style="max-height: 100px; border: 1px solid #eee; padding: 5px;">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="minimal-section" style="border: 2px solid #0284c7;">
                        <h3 class="minimal-section-title" style="color: #0284c7;">For Verification by AITF Assessor</h3>
                        <form method="post">
                            <input type="hidden" name="action" value="save_assessor_review">
                            <input type="hidden" name="id" value="<?php echo (int)$submission['id']; ?>">
                            
                            <div class="minimal-field">
                                <span class="minimal-label">Is the root cause analysis, correction, and corrective action acceptable?</span>
                                <div class="radio-group">
                                    <label><input type="radio" name="assessor_accepted" value="Yes" <?php echo ($submission['assessor_accepted'] == 'Yes') ? 'checked' : ''; ?>> Yes</label>
                                    <label><input type="radio" name="assessor_accepted" value="No" <?php echo ($submission['assessor_accepted'] == 'No') ? 'checked' : ''; ?>> No</label>
                                    <label><input type="radio" name="assessor_accepted" value="Further action" <?php echo ($submission['assessor_accepted'] == 'Further action') ? 'checked' : ''; ?>> Further action</label>
                                </div>
                            </div>

                            <!-- YES GROUP -->
                            <div id="assessor_yes_group" style="display: none;">
                                <?php 
                                $methods = explode(", ", $submission['assessor_verification_method'] ?? '');
                                ?>
                                <div class="minimal-field">
                                    <span class="minimal-label">Verification will be by</span>
                                    <div class="checkbox-group">
                                        <label><input type="checkbox" name="assessor_verification_method[]" value="Correction & corrective action received" <?php echo in_array('Correction & corrective action received', $methods) ? 'checked' : ''; ?>> Correction & corrective action received (for minor NC*)</label><br>
                                        <label><input type="checkbox" name="assessor_verification_method[]" value="Verification of supporting evidence/s" <?php echo in_array('Verification of supporting evidence/s', $methods) ? 'checked' : ''; ?>> Verification of supporting evidence/s</label><br>
                                        <label><input type="checkbox" name="assessor_verification_method[]" value="Onsite verification" <?php echo in_array('Onsite verification', $methods) ? 'checked' : ''; ?>> Onsite verification</label>
                                    </div>
                                </div>

                                <div class="minimal-grid">
                                    <div class="minimal-field">
                                        <span class="minimal-label">Team Leader / Assessor Name</span>
                                        <input type="text" name="team_leader_name" class="form-control" value="<?php echo htmlspecialchars($submission['team_leader_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="minimal-field">
                                        <span class="minimal-label">Signature / Date</span>
                                        <input type="text" name="team_leader_signature" class="form-control" placeholder="Signature" style="margin-bottom: 5px;" value="<?php echo htmlspecialchars($submission['team_leader_signature'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="date" name="team_leader_date" class="form-control" value="<?php echo htmlspecialchars($submission['team_leader_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- NO GROUP -->
                            <div id="assessor_no_group" style="display: none;">
                                <div class="minimal-field">
                                    <span class="minimal-label">Comments (if any)</span>
                                    <textarea name="assessor_comments" class="form-control"><?php echo htmlspecialchars($submission['assessor_comments'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="minimal-grid">
                                    <div class="minimal-field">
                                        <span class="minimal-label">Team Leader / Assessor Name</span>
                                        <input type="text" name="assessor_name" class="form-control" value="<?php echo htmlspecialchars($submission['assessor_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="minimal-field">
                                        <span class="minimal-label">Signature / Date</span>
                                        <input type="text" name="assessor_signature" class="form-control" placeholder="Signature" style="margin-bottom: 5px;" value="<?php echo htmlspecialchars($submission['assessor_signature'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="date" name="assessor_date" class="form-control" value="<?php echo htmlspecialchars($submission['assessor_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="minimal-field" style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 20px;">
                                <span class="minimal-label">Verification of effectiveness of corrective action (During follow up audit/Next routine audit)</span>
                                <div class="radio-group" style="margin-bottom: 10px;">
                                    <label><input type="radio" name="follow_up_effectiveness" value="Satisfactory - Yes" <?php echo ($submission['follow_up_effectiveness'] == 'Satisfactory - Yes') ? 'checked' : ''; ?>> Satisfactory - Yes</label>
                                    <label><input type="radio" name="follow_up_effectiveness" value="Satisfactory - No" <?php echo ($submission['follow_up_effectiveness'] == 'Satisfactory - No') ? 'checked' : ''; ?>> Satisfactory - No</label>
                                </div>
                            </div>

                            <div class="minimal-grid">
                                <div class="minimal-field">
                                    <span class="minimal-label">Verifying Assessor Sign</span>
                                    <input type="text" name="verifying_assessor_sign" class="form-control" value="<?php echo htmlspecialchars($submission['verifying_assessor_sign'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="minimal-field">
                                    <span class="minimal-label">Verifying Assessor Date</span>
                                    <input type="date" name="verifying_assessor_date" class="form-control" value="<?php echo htmlspecialchars($submission['verifying_assessor_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>

                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn-outline btn-primary"><i class="fa fa-save"></i> Save Assessor Review</button>
                            </div>
                        </form>
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
                    <div style="padding: 20px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; color: #92400e;">
                        Not Found: The requested NC Closing form submission could not be found.<br><br>
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

    $(document).ready(function() {
        function toggleAssessorFields() {
            var val = $('input[name="assessor_accepted"]:checked').val();
            if (val === 'Yes') {
                $('#assessor_yes_group').show();
                $('#assessor_no_group').hide();
            } else if (val === 'No' || val === 'Further action') {
                $('#assessor_yes_group').hide();
                $('#assessor_no_group').show();
            } else {
                $('#assessor_yes_group').hide();
                $('#assessor_no_group').hide();
            }
        }
        
        $('input[name="assessor_accepted"]').change(toggleAssessorFields);
        toggleAssessorFields();
    });
</script>

</div>

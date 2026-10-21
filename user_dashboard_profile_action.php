<?php
/**
 * AJAX Controller for Updating User Profile Records on My Dashboard
 * 
 * Restricted strictly to:
 * - Top Mgmt
 * - Admin (or Main Admin)
 * - Finance
 * - Director
 * 
 * Permitted Fields ONLY:
 * 1. Applicable Records & Documents (applicable_records)
 * 2. Resource Issue Record (resource_issue_record)
 * 3. Work Description & Responsibilities (work_description)
 * 4. Email Address (email_id)
 * 
 * STRICT SECURITY: User Name, Role, Password, Employee ID, etc. CANNOT be modified via this controller.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/task_lib.php';

date_default_timezone_set('Asia/Kolkata');

// 1. Session and Authentication Verification
$actualLoggedInUserId = (int)($_SESSION['id'] ?? 0);
$actualLoggedInRole = (string)($_SESSION['role'] ?? '');
$actualLoggedInUsername = (string)($_SESSION['username'] ?? '');

if ($actualLoggedInUserId <= 0 && empty($actualLoggedInUsername)) {
    echo json_encode([
        'success' => false,
        'error' => 'Your session has expired. Please refresh and log in again.'
    ]);
    exit;
}

// 2. Authorization Check (Top Mgmt, Admin, Finance, Director, BDM)
$canEditProfile = in_array(strtolower(trim($actualLoggedInRole)), ['top mgmt', 'mgmt', 'director', 'admin', 'main admin', 'finance', 'bdm'], true)
    || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()))
    || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole));

if (!function_exists('aitf_is_role_top_mgmt')) {
    function aitf_is_role_top_mgmt($roleName) {
        $r = strtolower(trim((string)$roleName));
        return in_array($r, ['top mgmt', 'mgmt', 'director', 'main admin'], true);
    }
}
$isActorTopMgmt = aitf_is_role_top_mgmt($actualLoggedInRole)
    || (function_exists('aitf_is_top_management_role') && aitf_is_top_management_role($actualLoggedInRole))
    || (class_exists('Utitlity') && (Utitlity::checkForDirector() || Utitlity::checkForUserMainAdmin()));

if (!$canEditProfile) {
    echo json_encode([
        'success' => false,
        'error' => 'Permission denied. Only Top Management, Admin, Finance, or BDM roles are permitted to update these profile records.'
    ]);
    exit;
}

// Helper to normalize and clean section entries (JSON or raw text)
function aitf_clean_section_payload($raw) {
    if (is_array($raw)) {
        $cleanItems = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $h = trim((string)($item['header'] ?? ''));
                $d = trim((string)($item['desc'] ?? ''));
                if ($h !== '' || $d !== '') {
                    $cleanItems[] = [
                        'header' => $h,
                        'desc' => $d
                    ];
                }
            }
        }
        return !empty($cleanItems) ? json_encode($cleanItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    }

    $raw = trim((string)$raw);
    if ($raw === '') {
        return '';
    }

    if ($raw[0] === '[' || $raw[0] === '{') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $cleanItems = [];
            foreach ($decoded as $item) {
                if (is_array($item)) {
                    $h = trim((string)($item['header'] ?? ''));
                    $d = trim((string)($item['desc'] ?? ''));
                    if ($h !== '' || $d !== '') {
                        $cleanItems[] = [
                            'header' => $h,
                            'desc' => $d
                        ];
                    }
                }
            }
            return !empty($cleanItems) ? json_encode($cleanItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        }
    }

    return $raw;
}

$action = trim((string)($_REQUEST['action'] ?? 'update'));

if ($action === 'get_profile_records') {
    $targetUid = (int)($_GET['user_id'] ?? 0);
    $targetAudId = (int)($_GET['auditor_id'] ?? 0);

    if ($targetUid <= 0 && $targetAudId <= 0) {
        $targetUid = $actualLoggedInUserId;
    }

    $userRow = null;
    if ($targetUid > 0) {
        $stmt = $db->prepare("SELECT user_id, user_name, email_id, role, applicable_records, work_description, resource_issue_record FROM user WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $targetUid]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($targetAudId > 0) {
        $audStmt = $db->prepare("SELECT auditors_id, auditor_name, auditor_email, login_email FROM auditors WHERE auditors_id = :aid LIMIT 1");
        $audStmt->execute([':aid' => $targetAudId]);
        if ($aud = $audStmt->fetch(PDO::FETCH_ASSOC)) {
            $audEmail = !empty($aud['login_email']) ? $aud['login_email'] : ($aud['auditor_email'] ?? '');
            $uStmt = $db->prepare("SELECT user_id, user_name, email_id, role, applicable_records, work_description, resource_issue_record FROM user WHERE LOWER(email_id) = LOWER(:email) OR LOWER(user_name) = LOWER(:uname) LIMIT 1");
            $uStmt->execute([':email' => $audEmail, ':uname' => $aud['auditor_name']]);
            $userRow = $uStmt->fetch(PDO::FETCH_ASSOC);
            if (!$userRow) {
                $userRow = [
                    'user_id' => 0,
                    'user_name' => $aud['auditor_name'],
                    'email_id' => $audEmail,
                    'role' => 'Auditor',
                    'applicable_records' => '',
                    'work_description' => '',
                    'resource_issue_record' => ''
                ];
            }
        }
    }

    if (!$userRow) {
        echo json_encode(['success' => false, 'error' => 'User profile not found.']);
        exit;
    }

    // Role check: Only Top Management can view Top Management profile records
    $targetRole = trim((string)($userRow['role'] ?? ''));
    if (!$isActorTopMgmt && aitf_is_role_top_mgmt($targetRole)) {
        echo json_encode(['success' => false, 'error' => 'Permission denied. Admin, Finance, and BDM roles cannot view Top Management records.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'profile' => [
            'user_id' => (int)($userRow['user_id'] ?? 0),
            'user_name' => $userRow['user_name'] ?? '',
            'email_id' => $userRow['email_id'] ?? '',
            'role' => $userRow['role'] ?? '',
            'applicable_records' => $userRow['applicable_records'] ?? '',
            'work_description' => $userRow['work_description'] ?? '',
            'resource_issue_record' => $userRow['resource_issue_record'] ?? ''
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// Action: Update Allowed Profile Records & Email
// -------------------------------------------------------------
if ($action === 'update' || $action === 'update_profile_records') {
    $targetUid = (int)($_POST['target_user_id'] ?? 0);
    $targetAudId = (int)($_POST['target_auditor_id'] ?? 0);
    $section = trim((string)($_POST['section'] ?? 'all'));

    if ($targetUid <= 0 && $targetAudId <= 0) {
        $targetUid = $actualLoggedInUserId;
    }

    // Resolve or find the user row in user table
    $existingUser = null;
    if ($targetUid > 0) {
        $stmt = $db->prepare("SELECT * FROM user WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $targetUid]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($targetAudId > 0) {
        $audStmt = $db->prepare("SELECT * FROM auditors WHERE auditors_id = :aid LIMIT 1");
        $audStmt->execute([':aid' => $targetAudId]);
        $audRow = $audStmt->fetch(PDO::FETCH_ASSOC);
        if ($audRow) {
            $audEmail = !empty($audRow['login_email']) ? $audRow['login_email'] : ($audRow['auditor_email'] ?? '');
            $uStmt = $db->prepare("SELECT * FROM user WHERE (LOWER(email_id) = LOWER(:email) AND email_id != '') OR (e_id = :eid AND e_id != '') OR LOWER(user_name) = LOWER(:uname) LIMIT 1");
            $uStmt->execute([':email' => $audEmail, ':eid' => $audRow['auditor_number'] ?? '', ':uname' => $audRow['auditor_name']]);
            $existingUser = $uStmt->fetch(PDO::FETCH_ASSOC);

            // If not found in user table, insert a matching row so profile records persist
            if (!$existingUser) {
                try {
                    $ins = $db->prepare("INSERT INTO user (user_name, abbreviation, email_id, role, e_id, designation_assigned, applicable_records, work_description, resource_issue_record) 
                        VALUES (:uname, :abbr, :email, 'Auditor', :eid, 'Senior Auditor', '', '', '')");
                    $ins->execute([
                        ':uname' => $audRow['auditor_name'],
                        ':abbr'  => $audRow['abbrevation'] ?? '',
                        ':email' => $audEmail,
                        ':eid'   => $audRow['auditor_number'] ?? ''
                    ]);
                    $targetUid = (int)$db->lastInsertId();
                    $stmt = $db->prepare("SELECT * FROM user WHERE user_id = :uid LIMIT 1");
                    $stmt->execute([':uid' => $targetUid]);
                    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (Throwable $e) {
                    error_log("Failed to create user record for auditor: " . $e->getMessage());
                }
            } else {
                $targetUid = (int)$existingUser['user_id'];
            }
        }
    }

    if (!$existingUser && $targetUid > 0) {
        echo json_encode(['success' => false, 'error' => 'Target user account not found in database.']);
        exit;
    }

    // Role check: Only Top Management can modify Top Management profile records
    if ($existingUser) {
        $targetRole = trim((string)($existingUser['role'] ?? ''));
        if (!$isActorTopMgmt && aitf_is_role_top_mgmt($targetRole)) {
            echo json_encode([
                'success' => false,
                'error' => 'Permission denied. Admin, Finance, and BDM roles cannot modify Top Management records.'
            ]);
            exit;
        }
    }

    $updatedFields = [];
    $updateParams = [':uid' => $targetUid];
    $newEmailToReturn = null;

    // A. Update Email Address if included in request
    if (isset($_POST['email_id']) && ($section === 'all' || $section === 'email')) {
        $cleanEmail = trim((string)$_POST['email_id']);
        if ($cleanEmail === '') {
            echo json_encode(['success' => false, 'error' => 'Email address cannot be empty.']);
            exit;
        }
        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please provide a valid email address (e.g. name@aitf.co.in).']);
            exit;
        }

        // Check if email already used by a different user
        $dupStmt = $db->prepare("SELECT user_id, user_name FROM user WHERE LOWER(email_id) = LOWER(:email) AND user_id != :uid LIMIT 1");
        $dupStmt->execute([':email' => $cleanEmail, ':uid' => $targetUid]);
        if ($dup = $dupStmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['success' => false, 'error' => 'The email "' . htmlspecialchars($cleanEmail) . '" is already registered to user "' . htmlspecialchars($dup['user_name']) . '".']);
            exit;
        }

        $updatedFields[] = "email_id = :email_id";
        $updateParams[':email_id'] = $cleanEmail;
        $newEmailToReturn = $cleanEmail;

        // If target is currently logged in user, update session email
        if ($targetUid === $actualLoggedInUserId) {
            $_SESSION['email'] = $cleanEmail;
        }

        // Also update auditors table if this user is linked to an auditor
        if ($targetAudId > 0) {
            try {
                $audUp = $db->prepare("UPDATE auditors SET auditor_email = :email, login_email = :loginEmail WHERE auditors_id = :aid");
                $audUp->execute([':email' => $cleanEmail, ':loginEmail' => $cleanEmail, ':aid' => $targetAudId]);
            } catch (Throwable $e) {}
        } else {
            try {
                $audUp = $db->prepare("UPDATE auditors SET auditor_email = :email, login_email = :loginEmail WHERE LOWER(auditor_email) = LOWER(:oldEmail) OR LOWER(login_email) = LOWER(:oldEmail)");
                $audUp->execute([
                    ':email' => $cleanEmail,
                    ':loginEmail' => $cleanEmail,
                    ':oldEmail' => $existingUser['email_id'] ?? ''
                ]);
            } catch (Throwable $e) {}
        }
    }

    // B. Update Applicable Records & Documents
    if (isset($_POST['applicable_records']) && ($section === 'all' || $section === 'applicable_records')) {
        $cleanAppRec = aitf_clean_section_payload($_POST['applicable_records']);
        $updatedFields[] = "applicable_records = :applicable_records";
        $updateParams[':applicable_records'] = $cleanAppRec;
    }

    // C. Update Resource Issue Record
    if (isset($_POST['resource_issue_record']) && ($section === 'all' || $section === 'resource_issue_record')) {
        $cleanResIssue = aitf_clean_section_payload($_POST['resource_issue_record']);
        $updatedFields[] = "resource_issue_record = :resource_issue_record";
        $updateParams[':resource_issue_record'] = $cleanResIssue;
    }

    // D. Update Work Description & Responsibilities
    if (isset($_POST['work_description']) && ($section === 'all' || $section === 'work_description')) {
        $cleanWorkDesc = aitf_clean_section_payload($_POST['work_description']);
        $updatedFields[] = "work_description = :work_description";
        $updateParams[':work_description'] = $cleanWorkDesc;
    }

    if (empty($updatedFields)) {
        echo json_encode(['success' => false, 'error' => 'No valid fields provided for update.']);
        exit;
    }

    try {
        $sql = "UPDATE user SET " . implode(", ", $updatedFields) . " WHERE user_id = :uid";
        $stmt = $db->prepare($sql);
        $stmt->execute($updateParams);

        echo json_encode([
            'success' => true,
            'message' => 'Profile records updated successfully!',
            'target_user_id' => $targetUid,
            'updated_email' => $newEmailToReturn,
            'section' => $section
        ]);
        exit;
    } catch (Throwable $e) {
        error_log("Error updating profile records: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Database error while updating profile records: ' . $e->getMessage()
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action request.']);
exit;

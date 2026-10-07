<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
include_once __DIR__ . '/dbConnect.php';
date_default_timezone_set('Asia/Kolkata');
include_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
$auditorHelperPath = __DIR__ . '/include/auditor_portal_helper.php';
if (file_exists($auditorHelperPath)) { require_once $auditorHelperPath; }

function aitf_redirect_and_exit($location)
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header("Location: " . $location);
    exit;
}

if (isset($_POST["name"]) && !empty($_POST["name"]) && isset($_POST["cno"]) && !empty($_POST["cno"])) {
    try {
        $rawCertNo = trim($_POST['cno']);
        $normCertNo = strtoupper(preg_replace("/[^A-Za-z0-9]+/", "", $rawCertNo));
        $customerinfo = false;
        $sqlCdcOffice = "SELECT ci.* FROM cdc_report_review_office_use cu INNER JOIN cdc_report_review crr ON crr.crr_id = cu.crr_id INNER JOIN customer_info ci ON ci.id = crr.customer_id WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(cu.certificate_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm ORDER BY cu.id DESC LIMIT 1";
        $statement = $db->prepare($sqlCdcOffice);
        $statement->bindParam(':norm', $normCertNo);
        $statement->execute();
        $customerinfo = $statement->fetch();
        if (!$customerinfo) {
            $sqlCertPrint = "SELECT ci.* FROM certificate_print cp INNER JOIN customer_info ci ON ci.id = cp.customer_id WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(cp.certificate_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm ORDER BY cp.certificate_id DESC LIMIT 1";
            $statement = $db->prepare($sqlCertPrint);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }
        if (!$customerinfo) {
            $sqlCdcOld = "SELECT ci.* FROM cdc_report_review crr INNER JOIN customer_info ci ON ci.id = crr.customer_id WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(crr.certificateNo, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm ORDER BY crr.crr_id DESC LIMIT 1";
            $statement = $db->prepare($sqlCdcOld);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }
        if (!$customerinfo) {
            $customer = "SELECT * FROM `customer_info` WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(certification_no, ' ', ''), '/', ''), '-', ''), '.', '')) = :norm ORDER BY id DESC LIMIT 1";
            $statement = $db->prepare($customer);
            $statement->bindParam(':norm', $normCertNo);
            $statement->execute();
            $customerinfo = $statement->fetch();
        }
        if ($customerinfo && isset($customerinfo['id'])) {
            $_SESSION['username'] = "External User";
            $_SESSION['id'] = $customerinfo['id'];
            $_SESSION['role'] = "External";
            aitf_mark_session_authenticated();
            aitf_redirect_and_exit("certifiedClient.php");
        } else {
            $_SESSION['validation_error'] = "invalid_client_warning";
            aitf_redirect_and_exit("validation.php");
        }
    } catch (Exception $ex) {
        $_SESSION['validation_error'] = "invalid_client_warning";
        aitf_redirect_and_exit("validation.php");
    }
}

// Setup Rate limit table
$db->exec("CREATE TABLE IF NOT EXISTS login_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_identifier VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 0,
    lockout_until DATETIME DEFAULT NULL,
    UNIQUE KEY uniq_acc_role (account_identifier, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

function get_rate_limit($db, $identifier, $role) {
    $stmt = $db->prepare("SELECT * FROM login_rate_limits WHERE account_identifier = :id AND role = :role");
    $stmt->execute([':id' => strtolower($identifier), ':role' => $role]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function record_failed_attempt($db, $identifier, $role) {
    $rec = get_rate_limit($db, $identifier, $role);
    if ($rec) {
        $attempts = $rec['attempts'] + 1;
        $lockout = null;
        if ($attempts == 5) {
            $lockout = date('Y-m-d H:i:s', time() + 15 * 60);
        } elseif ($attempts >= 8) {
            $lockout = date('Y-m-d H:i:s', time() + 24 * 3600);
        }
        if ($lockout) {
            $stmt = $db->prepare("UPDATE login_rate_limits SET attempts = :attempts, lockout_until = :lockout WHERE id = :id");
            $stmt->execute([':attempts' => $attempts, ':lockout' => $lockout, ':id' => $rec['id']]);
        } else {
            $stmt = $db->prepare("UPDATE login_rate_limits SET attempts = :attempts WHERE id = :id");
            $stmt->execute([':attempts' => $attempts, ':id' => $rec['id']]);
        }
        return ['attempts' => $attempts, 'lockout' => $lockout];
    } else {
        $stmt = $db->prepare("INSERT INTO login_rate_limits (account_identifier, role, attempts) VALUES (:id, :role, 1)");
        $stmt->execute([':id' => strtolower($identifier), ':role' => $role]);
        return ['attempts' => 1, 'lockout' => null];
    }
}

function reset_failed_attempt($db, $identifier, $role) {
    $stmt = $db->prepare("DELETE FROM login_rate_limits WHERE account_identifier = :id AND role = :role");
    $stmt->execute([':id' => strtolower($identifier), ':role' => $role]);
}

function aitf_find_matching_clients($db, $identifier) {
    $raw = trim((string)$identifier);
    if ($raw === '') return [];
    $lower = strtolower($raw);
    $alphanumeric = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $raw));
    
    $results = [];
    $seenIds = [];
    
    $addClients = function($rows) use (&$results, &$seenIds) {
        foreach ($rows as $r) {
            if (!isset($seenIds[$r['id']])) {
                $seenIds[$r['id']] = true;
                $results[] = $r;
            }
        }
    };
    
    // 1. Direct exact match on unique_id or email_id or customer_name
    $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND (LOWER(unique_id) = :lower OR LOWER(email_id) = :lower OR LOWER(customer_name) = :lower) ORDER BY (unique_id IS NOT NULL AND unique_id != '') DESC, id DESC");
    $stmt->execute([':lower' => $lower]);
    $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // 2. Direct match on certification_aggrement.aggrement_no
    $stmt = $db->prepare("SELECT ci.* FROM certification_aggrement ca JOIN customer_info ci ON ca.customer_id = ci.id WHERE ci.is_deleted = 'NO' AND LOWER(ca.aggrement_no) = :lower ORDER BY ca.id DESC");
    $stmt->execute([':lower' => $lower]);
    $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // 3. Email search (handles trailing commas, spaces, multiple comma-separated emails)
    if (strpos($raw, '@') !== false) {
        $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(email_id) LIKE :email ORDER BY id DESC");
        $stmt->execute([':email' => '%' . $lower . '%']);
        while ($cand = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parts = preg_split('/[\s,;]+/', strtolower($cand['email_id'] ?? ''));
            if (in_array($lower, $parts, true)) {
                $addClients([$cand]);
            }
        }
    }
    
    // 4. Exact alphanumeric match on customer_name (ignores multiple spaces, dots, dashes, special chars)
    if (strlen($alphanumeric) >= 3) {
        $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(customer_name, '[^a-zA-Z0-9]', '')) = :norm ORDER BY (unique_id IS NOT NULL AND unique_id != '') DESC, id DESC");
        $stmt->execute([':norm' => $alphanumeric]);
        $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    // 5. Normalized agreement / unique ID match (ignoring dashes, slashes, spaces)
    if (strlen($alphanumeric) >= 4) {
        $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(unique_id, '[^a-zA-Z0-9]', '')) = :norm ORDER BY id DESC");
        $stmt->execute([':norm' => $alphanumeric]);
        $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        $stmt = $db->prepare("SELECT ci.* FROM certification_aggrement ca JOIN customer_info ci ON ca.customer_id = ci.id WHERE ci.is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(ca.aggrement_no, '[^a-zA-Z0-9]', '')) = :norm ORDER BY ca.id DESC");
        $stmt->execute([':norm' => $alphanumeric]);
        $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    // 6. Substring / Prefix match on alphanumeric name
    if (strlen($alphanumeric) >= 6) {
        $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(customer_name, '[^a-zA-Z0-9]', '')) LIKE :pref ORDER BY (unique_id IS NOT NULL AND unique_id != '') DESC, id DESC LIMIT 5");
        $stmt->execute([':pref' => $alphanumeric . '%']);
        $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        $strippedInput = preg_replace('/(privatelimited|pvtltd|ltd|limited|works|enterprises|industries)$/i', '', $alphanumeric);
        if (strlen($strippedInput) >= 5 && $strippedInput !== $alphanumeric) {
            $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(REGEXP_REPLACE(customer_name, '[^a-zA-Z0-9]', ''), '(privatelimited|pvtltd|ltd|limited|works|enterprises|industries)', '')) = :norm ORDER BY (unique_id IS NOT NULL AND unique_id != '') DESC, id DESC LIMIT 5");
            $stmt->execute([':norm' => $strippedInput]);
            $addClients($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    }
    
    return $results;
}

function aitf_verify_client_password($db, $client, $password) {
    $rawPass = trim((string)$password);
    if ($rawPass === '') return false;
    
    $normPass = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $rawPass));
    if (empty($normPass)) return false;
    
    $candidates = [];
    
    if (!empty($client['unique_id'])) {
        $candidates[] = $client['unique_id'];
    }
    
    $stmt = $db->prepare("SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid");
    $stmt->execute([':cid' => $client['id']]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['aggrement_no'])) {
            $candidates[] = $row['aggrement_no'];
        }
    }
    
    if (!empty($client['certification_no'])) {
        $candidates[] = $client['certification_no'];
    }
    
    // Also candidate: customer's normalized name (in case user entered client name as pass)
    $normClientName = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $client['customer_name']));
    if ($normPass === $normClientName) {
        return true;
    }
    if (min(strlen($normPass), strlen($normClientName)) >= 6) {
        if (strpos($normClientName, $normPass) === 0 || strpos($normPass, $normClientName) === 0) {
            return true;
        }
    }
    
    foreach ($candidates as $cand) {
        $normCand = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $cand));
        if ($normPass === $normCand) {
            return true;
        }
        if (min(strlen($normPass), strlen($normCand)) >= 6) {
            if (strpos($normCand, $normPass) === 0 || strpos($normPass, $normCand) === 0) {
                return true;
            }
        }
    }
    
    return false;
}

function send_lockout_email($db, $identifier, $role, $durationStr) {
    $email = ''; $name = 'User';
    if ($role === 'Director') {
        $email = trim((string)aitf_env('Director_id', aitf_env('DIRECTOR_ID', '')));
        $name = 'Managing Director';
    } elseif ($role === 'Employee') {
        $stmt = $db->prepare("SELECT email_id, user_name FROM user WHERE LOWER(email_id) = :id OR e_id = :id_upper LIMIT 1");
        $stmt->execute([':id' => strtolower($identifier), ':id_upper' => strtoupper($identifier)]);
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) { $email = $user['email_id']; $name = $user['user_name']; }
    } elseif ($role === 'Auditor') {
        $stmt = $db->prepare("SELECT login_email, auditor_name FROM auditors WHERE LOWER(login_email) = :id AND login_enabled = 1 LIMIT 1");
        $stmt->execute([':id' => strtolower($identifier)]);
        if ($aud = $stmt->fetch(PDO::FETCH_ASSOC)) { $email = $aud['login_email']; $name = $aud['auditor_name']; }
    } elseif ($role === 'Client') {
        $matching = aitf_find_matching_clients($db, $identifier);
        if (!empty($matching)) {
            $cli = $matching[0];
            $name = $cli['customer_name'];
            $emails = preg_split('/[\s,;]+/', trim($cli['email_id'] ?? ''));
            $email = !empty($emails[0]) ? $emails[0] : '';
        }
    }
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subject = 'Security Alert: AITF Account Locked';
        $body = "<p>Dear " . aitf_rbac_escape($name) . ",</p>"
              . "<p>Your AITF account has been temporarily locked for <strong>$durationStr</strong> due to multiple incorrect login attempts.</p>"
              . "<p>If this was not you, please contact the administrator immediately.</p>"
              . "<p>Regards,<br>AITF Management Certification Limited</p>";
        try { aitf_send_html_mail($email, $subject, $body); } catch (Exception $e) {}
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    if ($action === 'check_account') {
        $identifier = trim($_POST['identifier'] ?? '');
        if (empty($identifier)) {
            echo json_encode(['success' => false, 'message' => 'Please enter an email or ID.']);
            exit;
        }
        
        $foundRole = null;
        
        // 1. Director
        $directorEmail = trim((string)aitf_env('Director_id', aitf_env('DIRECTOR_ID', '')));
        if ($directorEmail !== '' && strtolower($directorEmail) === strtolower($identifier)) {
            $foundRole = 'Director';
        }
        
        // 2. User
        if (!$foundRole) {
            aitf_ensure_rbac_schema($db);
            $stmt = $db->prepare("SELECT * FROM user WHERE LOWER(email_id) = :id OR e_id = :id_upper LIMIT 1");
            $stmt->execute([':id' => strtolower($identifier), ':id_upper' => strtoupper($identifier)]);
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $foundRole = 'Employee';
            }
        }
        
        // 3. Auditor
        if (!$foundRole) {
            if (function_exists('aitf_ensure_auditor_portal_schema')) {
                aitf_ensure_auditor_portal_schema($db);
            }
            $stmt = $db->prepare("SELECT * FROM auditors WHERE LOWER(login_email) = :id AND login_enabled = 1 LIMIT 1");
            $stmt->execute([':id' => strtolower($identifier)]);
            if ($auditor = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $foundRole = 'Auditor';
            }
        }
        
        // 4. Client
        if (!$foundRole) {
            $matchingClients = aitf_find_matching_clients($db, $identifier);
            if (!empty($matchingClients)) {
                $foundRole = 'Client';
            }
        }
        
        if ($foundRole) {
            echo json_encode(['success' => true, 'role' => $foundRole]);
        } else {
            echo json_encode(['success' => false, 'message' => 'We cannot find an account with that username.']);
        }
        exit;
    }
    
    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        if (empty($identifier) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Missing credentials.']);
            exit;
        }
        
        // Check Rate limit
        $rl = get_rate_limit($db, $identifier, $role);
        if ($rl && $rl['lockout_until']) {
            $lockoutTime = strtotime($rl['lockout_until']);
            if (time() < $lockoutTime) {
                $remaining = ceil(($lockoutTime - time()) / 60);
                echo json_encode(['success' => false, 'message' => "Account locked due to too many failed attempts. Try again in $remaining minutes.", 'lockout_until' => $lockoutTime]);
                exit;
            } else {
                // lockout expired. Clear lockout_until so it doesn't block, but do NOT delete row to keep attempts
                $stmt = $db->prepare("UPDATE login_rate_limits SET lockout_until = NULL WHERE id = :id");
                $stmt->execute([':id' => $rl['id']]);
                $rl['lockout_until'] = null;
            }
        }
        
        $loginSuccess = false;
        $redirectUrl = 'myDashboard.php';
        
        if ($role === 'Director') {
            if (aitf_verify_director_credentials($identifier, $password)) {
                $_SESSION['username'] = 'Managing Director';
                $_SESSION['id'] = 0;
                $_SESSION['role'] = 'Director';
                $_SESSION['abbrevation'] = 'MD';
                aitf_mark_session_authenticated();
                aitf_record_login_activity($db, ['user_id'=>0, 'user_name'=>'Managing Director', 'email_id'=>$identifier, 'role'=>'Director', 'e_id'=>'DIRECTOR']);
                $loginSuccess = true;
                $redirectUrl = 'myDashboard.php';
            }
        } elseif ($role === 'Employee') {
            $stmt = $db->prepare("SELECT * FROM user WHERE LOWER(email_id) = :id OR e_id = :id_upper LIMIT 1");
            $stmt->execute([':id' => strtolower($identifier), ':id_upper' => strtoupper($identifier)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password'])) {
                if (($row['account_status'] ?? 'active') === 'inactive') {
                    echo json_encode(['success' => false, 'message' => 'Your account is inactive. Contact AITF Director.']);
                    exit;
                }
                if (($row['account_status'] ?? 'active') !== 'active') {
                    echo json_encode(['success' => false, 'message' => 'Please complete account creation first.']);
                    exit;
                }
                $_SESSION['username'] = $row['user_name'];
                $_SESSION['id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['abbrevation'] = $row['abbreviation'];
                aitf_mark_session_authenticated();
                aitf_record_login_activity($db, ['user_id'=>$row['user_id'], 'user_name'=>$row['user_name'], 'email_id'=>$row['email_id'], 'role'=>$row['role'], 'e_id'=>$row['e_id']]);
                
                if ($_SESSION['role'] === 'Auditor') {
                    $audStmt = $db->prepare("SELECT auditors_id, is_reviewer FROM auditors WHERE login_email = :email OR (auditor_number = :eid AND auditor_number != '') LIMIT 1");
                    $audStmt->execute([':email' => $row['email_id'], ':eid' => $row['e_id']]);
                    if ($audData = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['auditor_id'] = $audData['auditors_id'];
                        $_SESSION['is_reviewer'] = !empty($audData['is_reviewer']) ? 1 : 0;
                    }
                }
                
                $redirectUrl = 'myDashboard.php';
                $loginSuccess = true;
            }
        } elseif ($role === 'Auditor') {
            $stmt = $db->prepare("SELECT * FROM auditors WHERE LOWER(login_email) = :id AND login_enabled = 1 LIMIT 1");
            $stmt->execute([':id' => strtolower($identifier)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['login_key_hash'])) {
                $_SESSION['username'] = $row['auditor_name'];
                $_SESSION['id'] = $row['auditors_id'];
                $_SESSION['role'] = 'Auditor';
                $_SESSION['auditor_id'] = $row['auditors_id'];
                $_SESSION['is_reviewer'] = !empty($row['is_reviewer']) ? 1 : 0;
                $_SESSION['abbrevation'] = $row['abbrevation'] ?? '';
                aitf_mark_session_authenticated();
                aitf_record_login_activity($db, ['user_id'=>$row['auditors_id'], 'user_name'=>$row['auditor_name'], 'email_id'=>$row['login_email'], 'role'=>'Auditor', 'e_id'=>'AUDITOR']);
                $loginSuccess = true;
                $redirectUrl = 'myDashboard.php';
            }
        } elseif ($role === 'Client') {
            $matchingClients = aitf_find_matching_clients($db, $identifier);
            
            // Fallback: If not found by identifier, try finding by password (agreement no)
            if (empty($matchingClients)) {
                $rawPass = trim($password);
                $normPass = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $rawPass));
                if (!empty($normPass)) {
                    $stmt = $db->prepare("SELECT * FROM customer_info WHERE is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(unique_id, '[^a-zA-Z0-9]', '')) = :norm ORDER BY id DESC");
                    $stmt->execute([':norm' => strtolower($normPass)]);
                    $matchingClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($matchingClients)) {
                        $stmt = $db->prepare("SELECT ci.* FROM certification_aggrement ca JOIN customer_info ci ON ca.customer_id = ci.id WHERE ci.is_deleted = 'NO' AND LOWER(REGEXP_REPLACE(ca.aggrement_no, '[^a-zA-Z0-9]', '')) = :norm ORDER BY ca.id DESC");
                        $stmt->execute([':norm' => strtolower($normPass)]);
                        $matchingClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            }
            
            $matchedClient = null;
            foreach ($matchingClients as $clientCandidate) {
                if (aitf_verify_client_password($db, $clientCandidate, $password)) {
                    $matchedClient = $clientCandidate;
                    break;
                }
            }
            
            if ($matchedClient) {
                // If unique_id is empty on customer_info, auto-populate it with agreement number
                if (empty($matchedClient['unique_id']) && !empty($password)) {
                    try {
                        $db->prepare("UPDATE customer_info SET unique_id = :uid WHERE id = :id")->execute([':uid' => trim($password), ':id' => $matchedClient['id']]);
                        $matchedClient['unique_id'] = trim($password);
                    } catch (Exception $e) {}
                }
                
                $_SESSION['username'] = $matchedClient['customer_name'];
                $_SESSION['id'] = $matchedClient['id'];
                $_SESSION['role'] = "client";
                $_SESSION['uniqueid'] = !empty($matchedClient['unique_id']) ? $matchedClient['unique_id'] : trim($password);
                aitf_mark_session_authenticated();
                aitf_record_login_activity($db, [
                    'user_id' => $matchedClient['id'],
                    'user_name' => $matchedClient['customer_name'],
                    'email_id' => $matchedClient['email_id'] ?? '',
                    'role' => 'client',
                    'e_id' => $matchedClient['unique_id'] ?? ''
                ]);
                $loginSuccess = true;
                $redirectUrl = 'clientportal.php';
            }
        }
        
        if ($loginSuccess) {
            reset_failed_attempt($db, $identifier, $role);
            echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
        } else {
            $rlInfo = record_failed_attempt($db, $identifier, $role);
            $msg = 'Invalid password.';
            $lockoutTime = null;
            $sendEmail = false; $emailDuration = '';
            
            if ($rlInfo['attempts'] == 5) {
                $msg = 'Account locked due to 5 failed attempts. Try again in 15 minutes.';
                $lockoutTime = strtotime($rlInfo['lockout']);
                $sendEmail = true; $emailDuration = '15 minutes';
            } elseif ($rlInfo['attempts'] == 7) {
                $msg = 'Invalid password. Only one attempt left, after this account will be locked for 24 hrs.';
            } elseif ($rlInfo['attempts'] >= 8) {
                $msg = 'Account locked for 24 hours due to repeated failed attempts.';
                $lockoutTime = strtotime($rlInfo['lockout']);
                $sendEmail = true; $emailDuration = '24 hours';
            } else {
                if ($rlInfo['attempts'] < 5) {
                    $left = 5 - $rlInfo['attempts'];
                    $msg .= " $left attempts remaining before temporary lock.";
                }
            }
            if ($sendEmail) { send_lockout_email($db, $identifier, $role, $emailDuration); }
            echo json_encode(['success' => false, 'message' => $msg, 'lockout_until' => $lockoutTime]);
        }
        exit;
    }
    
    if ($action === 'create_request_otp') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false, 'message'=>'Invalid email address.']); exit; }
        if (strlen($password) < 8) { echo json_encode(['success'=>false, 'message'=>'Password must be at least 8 characters.']); exit; }
        if (!preg_match('/[A-Z]/', $password)) { echo json_encode(['success'=>false, 'message'=>'Password must contain uppercase letter.']); exit; }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) { echo json_encode(['success'=>false, 'message'=>'Password must contain special character.']); exit; }
        if ($password !== $confirmPassword) { echo json_encode(['success'=>false, 'message'=>'Passwords do not match.']); exit; }
        
        aitf_ensure_rbac_schema($db);
        $stmt = $db->prepare("SELECT * FROM user WHERE LOWER(email_id) = :email AND is_deleted = '0' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || empty($user['e_id'])) {
            echo json_encode(['success'=>false, 'message'=>'Account not allowed or not found.']); exit;
        }
        if (($user['account_status'] ?? 'active') === 'active') {
            echo json_encode(['success'=>false, 'message'=>'Account already active. Please sign in.']); exit;
        }
        
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 600);
        
        $db->prepare("UPDATE employee_signup_otps SET consumed_at = NOW() WHERE user_id = :user_id AND consumed_at IS NULL")->execute([':user_id' => $user['user_id']]);
        $db->prepare("INSERT INTO employee_signup_otps (user_id, email_id, otp_hash, expires_at) VALUES (:user_id, :email_id, :otp_hash, :expires_at)")
           ->execute([':user_id' => $user['user_id'], ':email_id' => $user['email_id'], ':otp_hash' => $otpHash, ':expires_at' => $expiresAt]);
        
        try { aitf_send_employee_otp_mail($user, $otp); } catch(Exception $e) {}
        
        $_SESSION['amazon_signup'] = [
            'user_id' => (int)$user['user_id'],
            'email_id' => $user['email_id'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ];
        
        echo json_encode(['success'=>true]);
        exit;
    }
    
    if ($action === 'create_complete') {
        $signup = $_SESSION['amazon_signup'] ?? [];
        $userId = (int)($signup['user_id'] ?? 0);
        $email = $signup['email_id'] ?? '';
        $otp = trim($_POST['otp'] ?? '');
        $eid = strtoupper(trim($_POST['eid'] ?? ''));
        
        if (!$userId || !$email) { echo json_encode(['success'=>false, 'message'=>'Session expired. Please start over.']); exit; }
        
        $stmt = $db->prepare("SELECT * FROM user WHERE user_id = :user_id AND LOWER(email_id) = :email LIMIT 1");
        $stmt->execute([':user_id' => $userId, ':email' => strtolower($email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || strtoupper((string)$user['e_id']) !== $eid) {
            echo json_encode(['success'=>false, 'message'=>'Employee ID does not match.']); exit;
        }
        
        $otpStmt = $db->prepare("SELECT otp_id, otp_hash FROM employee_signup_otps WHERE user_id = :user_id AND consumed_at IS NULL AND expires_at >= NOW() ORDER BY otp_id DESC LIMIT 1");
        $otpStmt->execute([':user_id' => $userId]);
        $otpRow = $otpStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$otpRow || !password_verify($otp, $otpRow['otp_hash'])) {
            echo json_encode(['success'=>false, 'message'=>'Invalid or expired OTP.']); exit;
        }
        
        $db->prepare("UPDATE user SET password = :password, account_status = 'active', password_created_at = NOW() WHERE user_id = :user_id")
           ->execute([':password' => $signup['password_hash'], ':user_id' => $userId]);
        $db->prepare("UPDATE employee_signup_otps SET consumed_at = NOW() WHERE otp_id = :otp_id")
           ->execute([':otp_id' => $otpRow['otp_id']]);
           
        unset($_SESSION['amazon_signup']);
        
        echo json_encode(['success'=>true]);
        exit;
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AITF Sign In</title>
    <style>
        body.aitf-login-page {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            flex-direction: column;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .login-page-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 40px 0;
            margin-top: 133px; /* 48px + 85px for nav */
        }
        .amazon-login-container { width: 100%; max-width: 350px; padding: 20px; margin: 0 auto; }
        .amazon-logo { text-align: center; margin-bottom: 20px; }
        .amazon-logo h1 { margin: 0; font-size: 28px; color: #ffffff; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .amazon-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 15px; }
        .amazon-box h2 { font-size: 28px; font-weight: 500; margin: 0 0 15px; color: #0f172a; }
        .amazon-login-container label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: #1e293b; }
        .amazon-login-container input[type="text"], .amazon-login-container input[type="email"], .amazon-login-container input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.05) inset; outline: none; }
        .amazon-login-container input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
        .amazon-btn { width: 100%; background: #3b82f6; border: 1px solid #2563eb; border-radius: 8px; padding: 10px; font-size: 14px; cursor: pointer; text-align: center; display: inline-block; font-weight: 500; color: #fff; text-decoration: none; box-sizing: border-box; transition: background 0.2s; }
        .amazon-btn:hover { background: #2563eb; }
        .amazon-btn-secondary { background: #fff; border: 1px solid #cbd5e1; color: #0f172a; }
        .amazon-btn-secondary:hover { background: #f1f5f9; }
        .amazon-alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; font-size: 13px; }
        .amazon-alert-error { color: #b91c1c; background-color: #fef2f2; border-color: #f87171; }
        .amazon-alert-success { color: #15803d; background-color: #f0fdf4; border-color: #86efac; }
        .amazon-divider { display: flex; align-items: center; text-align: center; color: #64748b; font-size: 12px; margin-bottom: 15px; }
        .amazon-divider::before, .amazon-divider::after { content: ''; flex: 1; border-bottom: 1px solid #e2e8f0; }
        .amazon-divider:not(:empty)::before { margin-right: .25em; }
        .amazon-divider:not(:empty)::after { margin-left: .25em; }
        .amazon-hidden { display: none !important; }
        .amazon-text-small { font-size: 12px; color: #475569; margin-top: 15px; line-height: 1.5; }
        .amazon-text-small a { color: #2563eb; text-decoration: none; }
        .amazon-text-small a:hover { color: #1d4ed8; text-decoration: underline; }
        .amazon-selected-identifier { display: flex; align-items: center; justify-content: space-between; font-size: 14px; margin-bottom: 15px; }
        .amazon-selected-identifier span { font-weight: bold; }
        .amazon-selected-identifier a { font-size: 13px; color: #2563eb; text-decoration: none; cursor: pointer; }
        .amazon-selected-identifier a:hover { text-decoration: underline; color: #1d4ed8; }
        .amazon-spinner { border: 3px solid rgba(255,255,255,0.3); border-top: 3px solid #fff; border-radius: 50%; width: 14px; height: 14px; animation: spin 1s linear infinite; display: inline-block; vertical-align: middle; margin-right: 5px; display: none; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="aitf-login-page" id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
<div class="login-page-wrapper">
<div class="amazon-login-container">
    <div class="amazon-logo">
        <h1 style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            AITF SIGN-IN
        </h1>
    </div>
    <?php
    $showError = false;
    $errorMsg = '';
    if (isset($_GET['reason'])) {
        $showError = true;
        if ($_GET['reason'] === 'login_required') $errorMsg = 'Please sign in to access this page.';
        else if ($_GET['reason'] === 'invalid_url') $errorMsg = 'Invalid URL or session expired.';
        else $errorMsg = 'There was a problem. Please sign in again.';
    }
?>
<?php if ($showError): ?>
    <div class="amazon-alert amazon-alert-error" style="margin-bottom: 20px;">
        <strong>Notice</strong><br>
        <?php echo htmlspecialchars($errorMsg); ?>
    </div>
<?php endif; ?>

    <div id="error-box" class="amazon-alert amazon-alert-error amazon-hidden">
        <strong id="error-title">There was a problem</strong><br>
        <span id="error-message"></span>
    </div>

    <!-- Sign In Panel 1: Identifier -->
    <div id="panel-signin-1" class="amazon-box">
        <h2>Sign in</h2>
        <form id="form-check" onsubmit="event.preventDefault(); checkAccount();">
            <label for="identifier">Username</label>
            <input type="text" id="identifier" name="identifier" required autocomplete="username" placeholder="Enter your username">
            <button type="submit" class="amazon-btn" id="btn-continue">
                <span class="amazon-spinner" id="spin-continue"></span> Continue
            </button>
        </form>
        <div class="amazon-text-small">
            By continuing, you agree to AITF's <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
        </div>
    </div>

    <!-- Sign In Panel 2: Password -->
    <div id="panel-signin-2" class="amazon-box amazon-hidden">
        <h2>Sign in</h2>
        <div class="amazon-selected-identifier">
            <span id="display-identifier"></span>
            <a onclick="showPanel('panel-signin-1');">Change</a>
        </div>
        <form id="form-login" onsubmit="event.preventDefault(); submitLogin();">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <label for="password" style="margin-bottom: 0;">Password</label>
                <a href="#" onclick="showError('To reset your password, please contact your Administrator or Managing Director.'); return false;" style="font-size: 13px; color: #2563eb; text-decoration: none;">Forgot your password?</a>
            </div>
            <div style="position: relative; margin-bottom: 15px;">
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" style="margin-bottom: 0; padding-right: 40px;">
                <span onclick="togglePassword('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b; display: flex;" title="Show password">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </span>
            </div>
            <button type="submit" class="amazon-btn" id="btn-signin">
                <span class="amazon-spinner" id="spin-signin"></span> Sign in
            </button>
        </form>
    </div>

    <!-- Create Account Divider -->
    <div id="create-divider" class="amazon-divider">New to AITF?</div>
    <button id="btn-create-nav" class="amazon-btn amazon-btn-secondary" onclick="showPanel('panel-create-1');">Create your AITF account</button>

    <!-- Create Account Panel 1: Details -->
    <div id="panel-create-1" class="amazon-box amazon-hidden">
        <h2>Create account</h2>
        <form id="form-create-1" onsubmit="event.preventDefault(); requestOtp();">
            <label for="create_email">Email</label>
            <input type="email" id="create_email" required>
            
            <label for="create_password">Password</label>
            <div style="position: relative; margin-bottom: 15px;">
                <input type="password" id="create_password" placeholder="At least 8 characters" required style="margin-bottom: 0; padding-right: 40px;">
                <span onclick="togglePassword('create_password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b; display: flex;" title="Show password">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </span>
            </div>
            <div style="font-size: 11px; color: #475569; margin-top: -10px; margin-bottom: 15px;">
                <i style="color: #3b82f6;">ℹ</i> Passwords must be at least 8 characters, contain one uppercase letter and one special character.
            </div>

            <label for="create_confirm">Re-enter password</label>
            <div style="position: relative; margin-bottom: 15px;">
                <input type="password" id="create_confirm" required style="margin-bottom: 0; padding-right: 40px;">
                <span onclick="togglePassword('create_confirm', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b; display: flex;" title="Show password">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </span>
            </div>
            
            <button type="submit" class="amazon-btn" id="btn-create-1">
                <span class="amazon-spinner" id="spin-create-1"></span> Continue
            </button>
        </form>
        <div class="amazon-text-small" style="margin-top: 20px;">
            Already have an account? <a href="#" onclick="showPanel('panel-signin-1'); return false;">Sign in ▸</a>
        </div>
    </div>

    <!-- Create Account Panel 2: OTP -->
    <div id="panel-create-2" class="amazon-box amazon-hidden">
        <h2>Verify email address</h2>
        <div class="amazon-text-small" style="margin-bottom: 15px; margin-top: 0;">
            To verify your email, we've sent a One Time Password (OTP) to <strong id="verify-email-display"></strong>.
        </div>
        <form id="form-create-2" onsubmit="event.preventDefault(); completeCreate();">
            <label for="create_otp">Enter OTP</label>
            <input type="text" id="create_otp" required>
            
            <label for="create_eid">Employee ID (EID)</label>
            <input type="text" id="create_eid" required>
            
            <button type="submit" class="amazon-btn" id="btn-create-2">
                <span class="amazon-spinner" id="spin-create-2"></span> Create your AITF account
            </button>
        </form>
    </div>

</div>
</div>

<script>
function togglePassword(inputId, el) {
    const input = document.getElementById(inputId);
    const eyeIcon = el.querySelector('.eye-icon');
    const eyeOffIcon = el.querySelector('.eye-off-icon');
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

let currentRole = '';
let currentIdentifier = '';
let lockoutTimer = null;

function showError(msg, type = 'error', lockoutUntil = null) {
    if (lockoutTimer) { clearInterval(lockoutTimer); lockoutTimer = null; }
    const box = document.getElementById('error-box');
    box.classList.remove('amazon-hidden', 'amazon-alert-error', 'amazon-alert-success');
    box.classList.add(type === 'error' ? 'amazon-alert-error' : 'amazon-alert-success');
    document.getElementById('error-title').innerText = type === 'error' ? 'There was a problem' : 'Success';
    
    if (lockoutUntil) {
        const updateTimer = () => {
            const now = Math.floor(Date.now() / 1000);
            const diff = lockoutUntil - now;
            if (diff <= 0) {
                clearInterval(lockoutTimer);
                document.getElementById('error-message').innerText = "Account lock expired. You can try again.";
            } else {
                const h = Math.floor(diff / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                let tStr = h > 0 ? h + "h " + m + "m " + s + "s" : m + "m " + s + "s";
                document.getElementById('error-message').innerText = "Account locked due to too many failed attempts. Try again in " + tStr + ".";
            }
        };
        updateTimer();
        lockoutTimer = setInterval(updateTimer, 1000);
    } else {
        document.getElementById('error-message').innerText = msg;
    }
}

function hideError() {
    document.getElementById('error-box').classList.add('amazon-hidden');
}

function showPanel(panelId) {
    hideError();
    document.querySelectorAll('.amazon-box').forEach(el => el.classList.add('amazon-hidden'));
    document.getElementById(panelId).classList.remove('amazon-hidden');
    
    if (panelId === 'panel-signin-1' || panelId === 'panel-signin-2') {
        document.getElementById('create-divider').classList.remove('amazon-hidden');
        document.getElementById('btn-create-nav').classList.remove('amazon-hidden');
    } else {
        document.getElementById('create-divider').classList.add('amazon-hidden');
        document.getElementById('btn-create-nav').classList.add('amazon-hidden');
    }
}

function checkAccount() {
    const identifier = document.getElementById('identifier').value.trim();
    if (!identifier) return;
    hideError();
    document.getElementById('spin-continue').style.display = 'inline-block';
    document.getElementById('btn-continue').disabled = true;

    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=check_account&identifier=' + encodeURIComponent(identifier)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('spin-continue').style.display = 'none';
        document.getElementById('btn-continue').disabled = false;
        if (data.success) {
            currentRole = data.role;
            currentIdentifier = identifier;
            document.getElementById('display-identifier').innerText = identifier;
            showPanel('panel-signin-2');
            document.getElementById('password').focus();
        } else {
            showError(data.message);
        }
    }).catch(e => {
        document.getElementById('spin-continue').style.display = 'none';
        document.getElementById('btn-continue').disabled = false;
        showError('Network error. Please try again.');
    });
}

function submitLogin() {
    const password = document.getElementById('password').value;
    if (!password) return;
    hideError();
    document.getElementById('spin-signin').style.display = 'inline-block';
    document.getElementById('btn-signin').disabled = true;

    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=login&identifier=' + encodeURIComponent(currentIdentifier) + '&role=' + encodeURIComponent(currentRole) + '&password=' + encodeURIComponent(password)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('spin-signin').style.display = 'none';
        document.getElementById('btn-signin').disabled = false;
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            showError(data.message, 'error', data.lockout_until);
            // Optionally clear password
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
        }
    }).catch(e => {
        document.getElementById('spin-signin').style.display = 'none';
        document.getElementById('btn-signin').disabled = false;
        showError('Network error. Please try again.');
    });
}

function requestOtp() {
    const email = document.getElementById('create_email').value;
    const pass = document.getElementById('create_password').value;
    const conf = document.getElementById('create_confirm').value;
    hideError();
    document.getElementById('spin-create-1').style.display = 'inline-block';
    document.getElementById('btn-create-1').disabled = true;

    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=create_request_otp&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(pass) + '&confirm_password=' + encodeURIComponent(conf)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('spin-create-1').style.display = 'none';
        document.getElementById('btn-create-1').disabled = false;
        if (data.success) {
            document.getElementById('verify-email-display').innerText = email;
            showPanel('panel-create-2');
        } else {
            showError(data.message);
        }
    }).catch(e => {
        document.getElementById('spin-create-1').style.display = 'none';
        document.getElementById('btn-create-1').disabled = false;
        showError('Network error. Please try again.');
    });
}

function completeCreate() {
    const otp = document.getElementById('create_otp').value;
    const eid = document.getElementById('create_eid').value;
    hideError();
    document.getElementById('spin-create-2').style.display = 'inline-block';
    document.getElementById('btn-create-2').disabled = true;

    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=create_complete&otp=' + encodeURIComponent(otp) + '&eid=' + encodeURIComponent(eid)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('spin-create-2').style.display = 'none';
        document.getElementById('btn-create-2').disabled = false;
        if (data.success) {
            showPanel('panel-signin-1');
            document.getElementById('identifier').value = document.getElementById('create_email').value;
            showError('Account created successfully! Please sign in.', 'success');
        } else {
            showError(data.message);
        }
    }).catch(e => {
        document.getElementById('spin-create-2').style.display = 'none';
        document.getElementById('btn-create-2').disabled = false;
        showError('Network error. Please try again.');
    });
}
</script>

<?php include 'footerSource.php'; ?>
</body>
</html>
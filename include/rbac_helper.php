<?php
require_once __DIR__ . '/env_helper.php';
require_once __DIR__ . '/session_helper.php';

if (!function_exists('aitf_rbac_escape')) {
    function aitf_rbac_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Internal application roles. Keep this list as the single source of truth for
 * user creation and server-side role validation.
 */
if (!function_exists('aitf_allowed_roles')) {
    function aitf_allowed_roles()
    {
        return array(
            'Top Mgmt',
            'Admin',
            'Finance',
            'Auditor',
            'Application Reviewer',
            'Report Reviewer',
            'Office Staff-A',
            'Office Staff-B',
            'Marketing Head',
            'Marketing Executive'
        );
    }
}

if (!function_exists('aitf_is_allowed_role')) {
    function aitf_is_allowed_role($role)
    {
        return in_array(trim((string)$role), aitf_allowed_roles(), true);
    }
}

if (!function_exists('aitf_is_top_management_role')) {
    function aitf_is_top_management_role($role = null)
    {
        $role = $role === null ? ($_SESSION['role'] ?? '') : $role;
        return trim((string)$role) === 'Top Mgmt';
    }
}

if (!function_exists('aitf_is_auditor_role')) {
    function aitf_is_auditor_role($role = null)
    {
        $role = $role === null ? ($_SESSION['role'] ?? '') : $role;
        // Auditor is retained only as a compatibility value for the separate
        // auditor portal; newly-created internal users use "Auditors".
        return in_array(trim((string)$role), array('Auditors', 'Auditor'), true);
    }
}

if (!function_exists('aitf_is_director_role')) {
    function aitf_is_director_role()
    {
        return aitf_is_top_management_role();
    }
}

if (!function_exists('aitf_verify_director_credentials')) {
    function aitf_verify_director_credentials($email, $password)
    {
        $directorEmail = trim((string)aitf_env('Director_id', aitf_env('DIRECTOR_ID', '')));
        $directorPassword = (string)aitf_env('Director_Password', aitf_env('DIRECTOR_PASSWORD', ''));

        return $directorEmail !== ''
            && $directorPassword !== ''
            && hash_equals(strtolower($directorEmail), strtolower(trim((string)$email)))
            && hash_equals($directorPassword, (string)$password);
    }
}

if (!function_exists('aitf_ensure_rbac_schema')) {
    function aitf_ensure_rbac_schema(PDO $db)
    {
        $statements = array(
            "ALTER TABLE user ADD COLUMN e_id VARCHAR(50) NULL",
            "ALTER TABLE user ADD COLUMN contact_no VARCHAR(50) NULL",
            "ALTER TABLE user ADD COLUMN account_status VARCHAR(20) NOT NULL DEFAULT 'active'",
            "ALTER TABLE user ADD COLUMN password_created_at DATETIME NULL",
            "ALTER TABLE user ADD UNIQUE KEY uniq_user_e_id (e_id)"
        );

        foreach ($statements as $sql) {
            try {
                $db->exec($sql);
            } catch (Exception $e) {
                // Column or key already exists.
            }
        }

        try {
            $db->exec("CREATE TABLE IF NOT EXISTS employee_signup_otps (
                otp_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                email_id VARCHAR(100) NOT NULL,
                otp_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                consumed_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_employee_signup_user (user_id),
                INDEX idx_employee_signup_email (email_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        } catch (Exception $e) {
            error_log('RBAC schema error: ' . $e->getMessage());
        }

        try {
            $db->exec("CREATE TABLE IF NOT EXISTS user_login_activity (
                activity_id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(128) NOT NULL,
                user_id INT NULL,
                user_name VARCHAR(150) NOT NULL,
                email_id VARCHAR(150) NULL,
                role VARCHAR(50) NOT NULL,
                e_id VARCHAR(80) NULL,
                login_at DATETIME NOT NULL,
                last_seen_at DATETIME NOT NULL,
                logout_at DATETIME NULL,
                ip_address VARCHAR(64) NULL,
                user_agent VARCHAR(255) NULL,
                INDEX idx_login_activity_active (logout_at, last_seen_at),
                INDEX idx_login_activity_day (login_at),
                INDEX idx_login_activity_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        } catch (Exception $e) {
            error_log('Login activity schema error: ' . $e->getMessage());
        }

        // Normalize the former management role before validating new sessions.
        try {
            $db->exec("UPDATE user SET role = 'Top Mgmt' WHERE role IN ('Director', 'Main Admin')");
        } catch (Exception $e) {
            error_log('RBAC role migration error: ' . $e->getMessage());
        }

        $roles = aitf_allowed_roles();
        foreach ($roles as $role) {
            try {
                $check = $db->prepare("SELECT COUNT(*) FROM user_role WHERE role = :role");
                $check->execute(array(':role' => $role));
                if ((int)$check->fetchColumn() === 0) {
                    $insert = $db->prepare("INSERT INTO user_role (role) VALUES (:role)");
                    $insert->execute(array(':role' => $role));
                }
            } catch (Exception $e) {
                error_log('RBAC role seed error: ' . $e->getMessage());
            }
        }
    }
}

if (!function_exists('aitf_record_login_activity')) {
    function aitf_record_login_activity(PDO $db, array $user)
    {
        aitf_ensure_rbac_schema($db);

        $sessionId = session_id();
        if ($sessionId === '') {
            return;
        }

        try {
            $db->prepare("UPDATE user_login_activity SET logout_at = NOW()
                          WHERE session_id = :session_id AND logout_at IS NULL")
                ->execute(array(':session_id' => $sessionId));

            $stmt = $db->prepare("INSERT INTO user_login_activity
                (session_id, user_id, user_name, email_id, role, e_id, login_at, last_seen_at, ip_address, user_agent)
                VALUES
                (:session_id, :user_id, :user_name, :email_id, :role, :e_id, NOW(), NOW(), :ip_address, :user_agent)");
            $stmt->execute(array(
                ':session_id' => $sessionId,
                ':user_id' => isset($user['user_id']) ? (int)$user['user_id'] : null,
                ':user_name' => (string)($user['user_name'] ?? ($_SESSION['username'] ?? 'User')),
                ':email_id' => $user['email_id'] ?? null,
                ':role' => (string)($user['role'] ?? ($_SESSION['role'] ?? 'User')),
                ':e_id' => $user['e_id'] ?? null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)
            ));
            $_SESSION['login_activity_id'] = (int)$db->lastInsertId();
        } catch (Exception $e) {
            error_log('Login activity record error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_touch_login_activity')) {
    function aitf_touch_login_activity(PDO $db)
    {
        if (empty($_SESSION['login_activity_id']) || empty($_SESSION['username'])) {
            return;
        }

        try {
            $stmt = $db->prepare("UPDATE user_login_activity
                                  SET last_seen_at = NOW()
                                  WHERE activity_id = :activity_id AND logout_at IS NULL");
            $stmt->execute(array(':activity_id' => (int)$_SESSION['login_activity_id']));
        } catch (Exception $e) {
            error_log('Login activity touch error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_record_logout_activity')) {
    function aitf_record_logout_activity(PDO $db)
    {
        if (empty($_SESSION['login_activity_id'])) {
            return;
        }

        try {
            $stmt = $db->prepare("UPDATE user_login_activity
                                  SET logout_at = NOW(), last_seen_at = NOW()
                                  WHERE activity_id = :activity_id AND logout_at IS NULL");
            $stmt->execute(array(':activity_id' => (int)$_SESSION['login_activity_id']));
        } catch (Exception $e) {
            error_log('Login activity logout error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_format_activity_duration')) {
    function aitf_format_activity_duration($start, $end = null)
    {
        $startTs = strtotime((string)$start);
        $endTs = $end ? strtotime((string)$end) : time();
        if (!$startTs || !$endTs || $endTs < $startTs) {
            return '-';
        }

        $seconds = $endTs - $startTs;
        $hours = (int)floor($seconds / 3600);
        $minutes = (int)floor(($seconds % 3600) / 60);
        $secs = (int)($seconds % 60);

        if ($hours > 0) {
            return $hours . 'h ' . str_pad((string)$minutes, 2, '0', STR_PAD_LEFT) . 'm';
        }
        if ($minutes > 0) {
            return $minutes . 'm ' . str_pad((string)$secs, 2, '0', STR_PAD_LEFT) . 's';
        }
        return $secs . 's';
    }
}

if (!function_exists('aitf_close_user_active_sessions')) {
    function aitf_close_user_active_sessions(PDO $db, $userId)
    {
        try {
            $stmt = $db->prepare("UPDATE user_login_activity
                                  SET logout_at = NOW(), last_seen_at = NOW()
                                  WHERE user_id = :user_id AND logout_at IS NULL");
            $stmt->execute(array(':user_id' => (int)$userId));
        } catch (Exception $e) {
            error_log('Login activity close user sessions error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_enforce_active_user_account')) {
    function aitf_enforce_active_user_account(PDO $db)
    {
        if (empty($_SESSION['username']) || empty($_SESSION['id']) || (int)$_SESSION['id'] <= 0) {
            return;
        }

        $role = (string)($_SESSION['role'] ?? '');
        if (in_array($role, array('Top Mgmt', 'client', 'External'), true)) {
            return;
        }

        try {
            $stmt = $db->prepare("SELECT account_status, is_deleted FROM user WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(array(':user_id' => (int)$_SESSION['id']));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || (string)$user['is_deleted'] !== '0' || (string)($user['account_status'] ?? 'active') !== 'active') {
                aitf_record_logout_activity($db);
                $_SESSION = array();
                session_regenerate_id(true);
                $_SESSION['login_notice'] = 'It seems there is a problem with your account. Contact AITF Director for more information.';
                header('Location: login.php?reason=login_required');
                exit;
            }
        } catch (Exception $e) {
            error_log('Active account enforcement error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_generate_employee_eid')) {
    function aitf_generate_employee_eid(PDO $db)
    {
        $year = date('Y');
        $prefix = 'AITF-EMP' . $year . '-';

        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digitsPool = '0123456789';
        
        do {
            $chars = '';
            for ($i = 0; $i < 4; $i++) {
                $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            
            $digits = '';
            for ($i = 0; $i < 4; $i++) {
                $digits .= $digitsPool[random_int(0, strlen($digitsPool) - 1)];
            }
            
            $candidate = $prefix . $chars . $digits;

            $check = $db->prepare("SELECT COUNT(*) FROM user WHERE e_id = :eid");
            $check->bindParam(':eid', $candidate);
            $check->execute();
            $exists = (int)$check->fetchColumn() > 0;
            
        } while ($exists);

        return $candidate;
    }
}

if (!function_exists('aitf_send_html_mail')) {
    function aitf_send_html_mail($toEmail, $subject, $bodyHtml)
    {
        require_once __DIR__ . '/invoice_mail_helper.php';
        aitfLoadPHPMailer();

        $mailer = new PHPMailer();
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $bodyHtml;
        $mailer->AltBody = trim(strip_tags(str_replace(array('<br>', '<br/>', '<br />'), "\n", $bodyHtml)));
        aitfAddEmailRecipients($mailer, $toEmail);

        list($sent, $error) = aitfSendInvoiceMailer($mailer);
        if (!$sent) {
            throw new Exception($error !== '' ? $error : 'Mail could not be sent.');
        }
    }
}

if (!function_exists('aitf_send_employee_eid_mail')) {
    function aitf_send_employee_eid_mail(array $user)
    {
        $body = '<div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">'
            . '<div style="background-color: #193048; color: #fff; padding: 20px; text-align: center;">'
            . '<h2 style="margin: 0; font-size: 24px;">Welcome to AITF Portal</h2>'
            . '</div>'
            . '<div style="padding: 30px;">'
            . '<p style="font-size: 16px;">Dear <strong>' . aitf_rbac_escape($user['user_name']) . '</strong>,</p>'
            . '<p style="font-size: 16px;">Your AITF Employee ID (EID) has been successfully generated. Below are your details:</p>'
            . '<div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #3b82f6;">'
            . '<p style="margin: 0 0 10px 0; font-size: 15px;"><strong>Email:</strong> ' . aitf_rbac_escape($user['email_id']) . '</p>'
            . '<p style="margin: 0; font-size: 15px;"><strong>Employee ID (EID):</strong> ' . aitf_rbac_escape($user['e_id']) . '</p>'
            . '</div>'
            . '<p style="font-size: 16px;">To activate your account and set your password, please click the button below to visit our login page.</p>'
            . '<div style="text-align: center; margin: 30px 0;">'
            . '<a href="https://aitf.co.in/login.php" style="background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 25px; font-size: 16px; border-radius: 5px; display: inline-block; font-weight: bold;">Login / Create Account</a>'
            . '</div>'
            . '<p style="font-size: 14px; color: #666;">Once on the login page (<a href="https://aitf.co.in/login.php" style="color: #3b82f6;">https://aitf.co.in/login.php</a>), please click on <strong>Create your AITF account</strong>, verify your email with OTP, and enter this EID to activate your account.</p>'
            . '<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">'
            . '<p style="font-size: 15px; margin-bottom: 5px;">Regards,</p>'
            . '<p style="font-size: 15px; font-weight: bold; margin-top: 0; color: #193048;">AITF Management Certification Limited</p>'
            . '</div></div>';

        aitf_send_html_mail($user['email_id'], 'AITF employee ID generated', $body);
    }
}

if (!function_exists('aitf_send_employee_otp_mail')) {
    function aitf_send_employee_otp_mail(array $user, $otp)
    {
        $body = '<p>Dear ' . aitf_rbac_escape($user['user_name']) . ',</p>'
            . '<p>Your OTP for AITF account creation is:</p>'
            . '<p style="font-size:22px;font-weight:bold;letter-spacing:4px;">' . aitf_rbac_escape($otp) . '</p>'
            . '<p>This OTP is valid for 10 minutes.</p>'
            . '<p>Regards,<br>AITF Management Certification Limited</p>';

        aitf_send_html_mail($user['email_id'], 'AITF account creation OTP', $body);
    }
}

if (!function_exists('aitf_redirect_user_by_role')) {
    function aitf_redirect_user_by_role()
    {
        include_once __DIR__ . '/../utility.php';

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor') {
            header('Location: auditorDashboard.php');
        } elseif (aitf_is_director_role()) {
            header('Location: directorSpace.php');
        } elseif (Utitlity::checkForUserFinance()) {
            return;
        }

        try {
            $stmt = $db->prepare("SELECT account_status, is_deleted FROM user WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(array(':user_id' => (int)$_SESSION['id']));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || (string)$user['is_deleted'] !== '0' || (string)($user['account_status'] ?? 'active') !== 'active') {
                aitf_record_logout_activity($db);
                $_SESSION = array();
                session_regenerate_id(true);
                $_SESSION['login_notice'] = 'It seems there is a problem with your account. Contact AITF Director for more information.';
                header('Location: login.php?reason=login_required');
                exit;
            }
        } catch (Exception $e) {
            error_log('Active account enforcement error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_generate_employee_eid')) {
    function aitf_generate_employee_eid(PDO $db)
    {
        $year = date('Y');
        $prefix = 'AITF-EMP' . $year . '-';

        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digitsPool = '0123456789';
        
        do {
            $chars = '';
            for ($i = 0; $i < 4; $i++) {
                $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            
            $digits = '';
            for ($i = 0; $i < 4; $i++) {
                $digits .= $digitsPool[random_int(0, strlen($digitsPool) - 1)];
            }
            
            $candidate = $prefix . $chars . $digits;

            $check = $db->prepare("SELECT COUNT(*) FROM user WHERE e_id = :eid");
            $check->bindParam(':eid', $candidate);
            $check->execute();
            $exists = (int)$check->fetchColumn() > 0;
            
        } while ($exists);

        return $candidate;
    }
}

if (!function_exists('aitf_send_html_mail')) {
    function aitf_send_html_mail($toEmail, $subject, $bodyHtml)
    {
        require_once __DIR__ . '/invoice_mail_helper.php';
        aitfLoadPHPMailer();

        $mailer = new PHPMailer();
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $bodyHtml;
        $mailer->AltBody = trim(strip_tags(str_replace(array('<br>', '<br/>', '<br />'), "\n", $bodyHtml)));
        aitfAddEmailRecipients($mailer, $toEmail);

        list($sent, $error) = aitfSendInvoiceMailer($mailer);
        if (!$sent) {
            throw new Exception($error !== '' ? $error : 'Mail could not be sent.');
        }
    }
}

if (!function_exists('aitf_send_employee_eid_mail')) {
    function aitf_send_employee_eid_mail(array $user)
    {
        $body = '<div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">'
            . '<div style="background-color: #193048; color: #fff; padding: 20px; text-align: center;">'
            . '<h2 style="margin: 0; font-size: 24px;">Welcome to AITF Portal</h2>'
            . '</div>'
            . '<div style="padding: 30px;">'
            . '<p style="font-size: 16px;">Dear <strong>' . aitf_rbac_escape($user['user_name']) . '</strong>,</p>'
            . '<p style="font-size: 16px;">Your AITF Employee ID (EID) has been successfully generated. Below are your details:</p>'
            . '<div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #3b82f6;">'
            . '<p style="margin: 0 0 10px 0; font-size: 15px;"><strong>Email:</strong> ' . aitf_rbac_escape($user['email_id']) . '</p>'
            . '<p style="margin: 0; font-size: 15px;"><strong>Employee ID (EID):</strong> ' . aitf_rbac_escape($user['e_id']) . '</p>'
            . '</div>'
            . '<p style="font-size: 16px;">To activate your account and set your password, please click the button below to visit our login page.</p>'
            . '<div style="text-align: center; margin: 30px 0;">'
            . '<a href="https://aitf.co.in/login.php" style="background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 25px; font-size: 16px; border-radius: 5px; display: inline-block; font-weight: bold;">Login / Create Account</a>'
            . '</div>'
            . '<p style="font-size: 14px; color: #666;">Once on the login page (<a href="https://aitf.co.in/login.php" style="color: #3b82f6;">https://aitf.co.in/login.php</a>), please click on <strong>Create your AITF account</strong>, verify your email with OTP, and enter this EID to activate your account.</p>'
            . '<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">'
            . '<p style="font-size: 15px; margin-bottom: 5px;">Regards,</p>'
            . '<p style="font-size: 15px; font-weight: bold; margin-top: 0; color: #193048;">AITF Management Certification Limited</p>'
            . '</div></div>';

        aitf_send_html_mail($user['email_id'], 'AITF employee ID generated', $body);
    }
}

if (!function_exists('aitf_send_employee_otp_mail')) {
    function aitf_send_employee_otp_mail(array $user, $otp)
    {
        $body = '<p>Dear ' . aitf_rbac_escape($user['user_name']) . ',</p>'
            . '<p>Your OTP for AITF account creation is:</p>'
            . '<p style="font-size:22px;font-weight:bold;letter-spacing:4px;">' . aitf_rbac_escape($otp) . '</p>'
            . '<p>This OTP is valid for 10 minutes.</p>'
            . '<p>Regards,<br>AITF Management Certification Limited</p>';

        aitf_send_html_mail($user['email_id'], 'AITF account creation OTP', $body);
    }
}

if (!function_exists('aitf_redirect_user_by_role')) {
    function aitf_redirect_user_by_role()
    {
        include_once __DIR__ . '/../utility.php';

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor') {
            header('Location: auditorDashboard.php');
        } elseif (aitf_is_director_role()) {
            header('Location: directorSpace.php');
        } elseif (Utitlity::checkForUserFinance()) {
            header('Location: paymentPage.php');
        } elseif (Utitlity::checkForUserMarketing()) {
            header('Location: marketingPage.php');
        } elseif (Utitlity::checkForUserConsultant()) {
            header('Location: auditPlanning.php');
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Report Reviewer') {
            header('Location: reportReviewerDashboard.php');
        } else {
            header('Location: homePage.php');
        }
        exit;
    }
}

<?php
/**
 * AITF Daily Session & Persistent Authentication Helper
 *
 * Provides a secure, app-like 24-hour persistent login mechanism:
 * 1. Upon successful login, generates a cryptographic device token stored in `aitf_auth_tokens`
 *    and sets a secure HttpOnly cookie `aitf_daily_session_token` with a 24-hour expiration.
 * 2. If a user closes their browser and revisits the site within 24 hours, the session is
 *    automatically and securely re-authenticated without manual login prompts.
 * 3. When an authenticated user visits the site or types "aitf" in their browser, they are
 *    automatically redirected to their dashboard (Staff/Director -> myDashboard.php, Client -> clientportal.php).
 * 4. Outside visitors ("bhar ka user") have NO token and are NEVER redirected to internal portals.
 * 5. Upon explicit logout, the token is revoked in the database and cleared from cookies.
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/env_helper.php';

if (!function_exists('aitf_ensure_auth_tokens_table')) {
    function aitf_ensure_auth_tokens_table($db)
    {
        static $checked = false;
        if ($checked || !$db instanceof PDO) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS `aitf_auth_tokens` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL DEFAULT 0,
                `role` VARCHAR(50) NOT NULL,
                `username` VARCHAR(255) NOT NULL DEFAULT '',
                `identifier` VARCHAR(255) NOT NULL DEFAULT '',
                `token_hash` VARCHAR(64) NOT NULL,
                `ip_address` VARCHAR(45) DEFAULT '',
                `user_agent` VARCHAR(255) DEFAULT '',
                `login_date` DATE NOT NULL,
                `created_at` DATETIME NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `is_revoked` TINYINT(1) NOT NULL DEFAULT 0,
                INDEX `idx_token_hash` (`token_hash`),
                INDEX `idx_user_role` (`user_id`, `role`),
                INDEX `idx_expires` (`expires_at`),
                INDEX `idx_revoked` (`is_revoked`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->exec($sql);
            $checked = true;
        } catch (Throwable $e) {
            error_log("Failed to ensure aitf_auth_tokens table: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_is_user_authenticated')) {
    function aitf_is_user_authenticated()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            aitf_start_session();
        }
        return !empty($_SESSION['username']) && !empty($_SESSION['role']);
    }
}

if (!function_exists('aitf_get_user_dashboard_url')) {
    function aitf_get_user_dashboard_url($role = null)
    {
        if ($role === null) {
            $role = $_SESSION['role'] ?? '';
        }
        $cleanRole = strtolower(trim((string)$role));
        if ($cleanRole === 'client') {
            return 'clientportal.php';
        }
        if ($cleanRole === 'external') {
            return 'certifiedClient.php';
        }
        return 'myDashboard.php';
    }
}

if (!function_exists('aitf_create_daily_session_token')) {
    function aitf_create_daily_session_token($db, $userId, $role, $username, $identifier)
    {
        if (!$db instanceof PDO) {
            return false;
        }

        aitf_ensure_auth_tokens_table($db);

        try {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
            $loginDate = date('Y-m-d');
            $createdAt = date('Y-m-d H:i:s');
            $lifetime = aitf_session_lifetime(); // 86400 (24 hours)
            $expiresAt = date('Y-m-d H:i:s', time() + $lifetime);

            // Invalidate older tokens for the same user on this device/ip
            try {
                $stmtDel = $db->prepare("DELETE FROM aitf_auth_tokens WHERE user_id = :uid AND role = :role AND ip_address = :ip");
                $stmtDel->execute([':uid' => (int)$userId, ':role' => (string)$role, ':ip' => $ip]);
            } catch (Throwable $e) {}

            $stmt = $db->prepare("INSERT INTO aitf_auth_tokens (user_id, role, username, identifier, token_hash, ip_address, user_agent, login_date, created_at, expires_at, is_revoked) VALUES (:uid, :role, :uname, :ident, :hash, :ip, :ua, :ldate, :created, :expires, 0)");
            $stmt->execute([
                ':uid' => (int)$userId,
                ':role' => (string)$role,
                ':uname' => (string)$username,
                ':ident' => (string)$identifier,
                ':hash' => $tokenHash,
                ':ip' => $ip,
                ':ua' => $ua,
                ':ldate' => $loginDate,
                ':created' => $createdAt,
                ':expires' => $expiresAt
            ]);

            // Set secure cookie for 24 hours
            $isSecure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
            $cookieParams = [
                'expires' => time() + $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            if (!headers_sent()) {
                setcookie('aitf_daily_session_token', $rawToken, $cookieParams);
            }
            $_COOKIE['aitf_daily_session_token'] = $rawToken;

            return true;
        } catch (Throwable $e) {
            error_log("Failed to create daily auth token: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_try_daily_token_login')) {
    function aitf_try_daily_token_login($db)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            aitf_start_session();
        }

        // If already authenticated, nothing to restore
        if (!empty($_SESSION['username']) && !empty($_SESSION['role'])) {
            return true;
        }

        $rawToken = $_COOKIE['aitf_daily_session_token'] ?? '';
        if (empty($rawToken) || !is_string($rawToken) || strlen($rawToken) < 32) {
            return false;
        }

        if (!$db instanceof PDO) {
            return false;
        }

        aitf_ensure_auth_tokens_table($db);

        try {
            $tokenHash = hash('sha256', $rawToken);
            $stmt = $db->prepare("SELECT * FROM aitf_auth_tokens WHERE token_hash = :hash AND is_revoked = 0 AND expires_at > NOW() LIMIT 1");
            $stmt->execute([':hash' => $tokenHash]);
            $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenRow) {
                // Token invalid or expired
                aitf_clear_daily_token_cookie();
                return false;
            }

            $userId = (int)$tokenRow['user_id'];
            $role = (string)$tokenRow['role'];
            $identifier = (string)$tokenRow['identifier'];
            $authenticated = false;

            // 1. Director
            if ($role === 'Director') {
                $directorEmail = trim((string)aitf_env('Director_id', aitf_env('DIRECTOR_ID', '')));
                if ($directorEmail !== '' && (strtolower($directorEmail) === strtolower($identifier) || $userId === 0)) {
                    $_SESSION['username'] = 'Managing Director';
                    $_SESSION['id'] = 0;
                    $_SESSION['role'] = 'Director';
                    $_SESSION['abbrevation'] = 'MD';
                    $authenticated = true;
                }
            }
            // 2. Client Portal
            elseif (strtolower($role) === 'client') {
                $stmtClient = $db->prepare("SELECT * FROM customer_info WHERE id = :id AND is_deleted = 'NO' LIMIT 1");
                $stmtClient->execute([':id' => $userId]);
                $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
                if ($client) {
                    $_SESSION['username'] = $client['customer_name'];
                    $_SESSION['id'] = (int)$client['id'];
                    $_SESSION['role'] = 'client';
                    $_SESSION['uniqueid'] = !empty($client['unique_id']) ? $client['unique_id'] : $identifier;
                    $authenticated = true;
                }
            }
            // 3. Auditor (dedicated auditor portal)
            elseif ($role === 'Auditor') {
                $stmtAud = $db->prepare("SELECT * FROM auditors WHERE auditors_id = :id AND login_enabled = 1 LIMIT 1");
                $stmtAud->execute([':id' => $userId]);
                $aud = $stmtAud->fetch(PDO::FETCH_ASSOC);
                if ($aud) {
                    $_SESSION['username'] = $aud['auditor_name'];
                    $_SESSION['id'] = (int)$aud['auditors_id'];
                    $_SESSION['role'] = 'Auditor';
                    $_SESSION['auditor_id'] = (int)$aud['auditors_id'];
                    $_SESSION['is_reviewer'] = !empty($aud['is_reviewer']) ? 1 : 0;
                    $_SESSION['abbrevation'] = $aud['abbrevation'] ?? '';
                    $authenticated = true;
                }
            }
            // 4. Employee / Admin / Top Mgmt / Staff user in `user` table
            else {
                $stmtUser = $db->prepare("SELECT * FROM user WHERE user_id = :id AND is_deleted = '0' LIMIT 1");
                $stmtUser->execute([':id' => $userId]);
                $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                if ($user && ($user['account_status'] ?? 'active') === 'active') {
                    $_SESSION['username'] = $user['user_name'];
                    $_SESSION['id'] = (int)$user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['abbrevation'] = $user['abbreviation'];

                    if ($user['role'] === 'Auditor') {
                        $audStmt = $db->prepare("SELECT auditors_id, is_reviewer FROM auditors WHERE login_email = :email OR (auditor_number = :eid AND auditor_number != '') LIMIT 1");
                        $audStmt->execute([':email' => $user['email_id'], ':eid' => $user['e_id']]);
                        if ($audData = $audStmt->fetch(PDO::FETCH_ASSOC)) {
                            $_SESSION['auditor_id'] = (int)$audData['auditors_id'];
                            $_SESSION['is_reviewer'] = !empty($audData['is_reviewer']) ? 1 : 0;
                        }
                    }
                    $authenticated = true;
                }
            }

            if ($authenticated) {
                aitf_mark_session_authenticated();
                return true;
            } else {
                // User account disabled or not found - revoke token
                $stmtRevoke = $db->prepare("UPDATE aitf_auth_tokens SET is_revoked = 1 WHERE id = :id");
                $stmtRevoke->execute([':id' => $tokenRow['id']]);
                aitf_clear_daily_token_cookie();
                return false;
            }
        } catch (Throwable $e) {
            error_log("Failed to restore daily auth token: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_revoke_daily_session_token')) {
    function aitf_revoke_daily_session_token($db = null)
    {
        $rawToken = $_COOKIE['aitf_daily_session_token'] ?? '';
        if (!empty($rawToken) && $db instanceof PDO) {
            try {
                $tokenHash = hash('sha256', $rawToken);
                $stmt = $db->prepare("UPDATE aitf_auth_tokens SET is_revoked = 1 WHERE token_hash = :hash");
                $stmt->execute([':hash' => $tokenHash]);
            } catch (Throwable $e) {}
        }
        aitf_clear_daily_token_cookie();
    }
}

if (!function_exists('aitf_clear_daily_token_cookie')) {
    function aitf_clear_daily_token_cookie()
    {
        if (!headers_sent()) {
            $isSecure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
            setcookie('aitf_daily_session_token', '', [
                'expires' => time() - 86400,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        unset($_COOKIE['aitf_daily_session_token']);
    }
}

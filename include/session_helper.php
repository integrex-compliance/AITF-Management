<?php
require_once __DIR__ . '/env_helper.php';

if (!function_exists('aitf_session_lifetime')) {
    function aitf_session_lifetime()
    {
        $lifetime = (int)aitf_env('AITF_SESSION_LIFETIME_SECONDS', 3600);
        return $lifetime > 0 ? $lifetime : 3600;
    }
}

if (!function_exists('aitf_session_cookie_options')) {
    function aitf_session_cookie_options()
    {
        $isSecure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';

        return [
            'expires' => time() + aitf_session_lifetime(),
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }
}

if (!function_exists('aitf_refresh_session_cookie')) {
    function aitf_refresh_session_cookie()
    {
        if (session_status() !== PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }

        setcookie(session_name(), session_id(), aitf_session_cookie_options());
    }
}

if (!function_exists('aitf_start_session')) {
    function aitf_start_session()
    {
        $lifetime = aitf_session_lifetime();
        ini_set('session.gc_maxlifetime', (string)$lifetime);
        ini_set('session.cookie_lifetime', (string)$lifetime);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $options = aitf_session_cookie_options();
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => $options['path'],
                'domain' => $options['domain'],
                'secure' => $options['secure'],
                'httponly' => $options['httponly'],
                'samesite' => $options['samesite']
            ]);
            session_start();
        }

        if (empty($_SESSION['username'])) {
            return true;
        }

        $now = time();
        $lastActivity = isset($_SESSION['aitf_last_activity'])
            ? (int)$_SESSION['aitf_last_activity']
            : $now;

        if (($now - $lastActivity) >= $lifetime) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['login_notice'] = 'Your session expired after one hour. Please log in again.';
            $_SESSION['session_expired'] = true;
            aitf_refresh_session_cookie();
            return false;
        }

        $_SESSION['aitf_last_activity'] = $now;
        aitf_refresh_session_cookie();
        return true;
    }
}

if (!function_exists('aitf_mark_session_authenticated')) {
    function aitf_mark_session_authenticated()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            aitf_start_session();
        }

        session_regenerate_id(true);
        $_SESSION['aitf_last_activity'] = time();
        unset($_SESSION['login_error'], $_SESSION['login_notice'], $_SESSION['session_expired']);
        aitf_refresh_session_cookie();
    }
}

if (!function_exists('aitf_destroy_session')) {
    function aitf_destroy_session()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            aitf_start_session();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}


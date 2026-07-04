<?php

if (!function_exists('aitf_env_load')) {
    function aitf_env_load()
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $envPath = dirname(__DIR__) . '/.env';
        if (!is_readable($envPath)) {
            $loaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            $loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                strlen($value) >= 2
                && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }

        $loaded = true;
    }
}

if (!function_exists('aitf_env')) {
    function aitf_env($key, $default = '')
    {
        aitf_env_load();

        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if ($value === false && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }

        if ($value === false || $value === null) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('aitf_env_bool')) {
    function aitf_env_bool($key, $default = false)
    {
        $value = aitf_env($key, null);
        if ($value === null || $value === '') {
            return (bool) $default;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('aitf_env_float')) {
    function aitf_env_float($key, $default = 0.0)
    {
        $value = aitf_env($key, null);
        return is_numeric($value) ? (float) $value : (float) $default;
    }
}

if (!function_exists('aitf_get_mail_config')) {
    function aitf_get_mail_config(array $overrides = [])
    {
        $config = [
            'host' => aitf_env('SMTP_HOST', ''),
            'port' => (int) aitf_env('SMTP_PORT', 587),
            'secure' => aitf_env('SMTP_SECURE', 'tls'),
            'username' => aitf_env('SMTP_USERNAME', ''),
            'password' => aitf_env('SMTP_PASSWORD', ''),
            'from_email' => aitf_env('MAIL_FROM_EMAIL', aitf_env('SMTP_USERNAME', '')),
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF'),
            'reply_to_email' => aitf_env('MAIL_REPLY_TO_EMAIL', aitf_env('MAIL_FROM_EMAIL', aitf_env('SMTP_USERNAME', ''))),
            'reply_to_name' => aitf_env('MAIL_REPLY_TO_NAME', aitf_env('MAIL_FROM_NAME', 'AITF')),
            'office_copy' => aitf_env('MAIL_OFFICE_COPY', aitf_env('MAIL_FROM_EMAIL', aitf_env('SMTP_USERNAME', ''))),
            'admin_email' => aitf_env('MAIL_ADMIN_EMAIL', aitf_env('MAIL_FROM_EMAIL', aitf_env('SMTP_USERNAME', ''))),
            'cc_email' => aitf_env('MAIL_CC_EMAIL', '')
        ];

        if ($config['port'] <= 0) {
            $config['port'] = 587;
        }

        return array_merge($config, $overrides);
    }
}

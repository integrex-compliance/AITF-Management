<?php
require_once __DIR__ . '/env_helper.php';

if (!function_exists('aitf_get_recaptcha_config')) {
    function aitf_get_recaptcha_config()
    {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $defaults = [
            'site_key' => aitf_env('RECAPTCHA_SITE_KEY', ''),
            'secret_key' => aitf_env('RECAPTCHA_SECRET_KEY', ''),
            'version' => aitf_env('RECAPTCHA_VERSION', 'v2_checkbox'),
            'action' => aitf_env('RECAPTCHA_ACTION', 'submit'),
            'minimum_score' => aitf_env_float('RECAPTCHA_MINIMUM_SCORE', 0.5),
            'bypass_on_localhost' => aitf_env_bool('RECAPTCHA_BYPASS_ON_LOCALHOST', false),
            'localhost_hosts' => ['localhost', '127.0.0.1', '::1'],
            'theme' => aitf_env('RECAPTCHA_THEME', 'light'),
            'enabled' => aitf_env_bool('RECAPTCHA_ENABLED', true),
            'use_test_keys_on_localhost' => aitf_env_bool('RECAPTCHA_USE_TEST_KEYS_ON_LOCALHOST', false),
            'local_test_site_key' => aitf_env('RECAPTCHA_LOCAL_TEST_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'),
            'local_test_secret_key' => aitf_env('RECAPTCHA_LOCAL_TEST_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'),
            'local_test_version' => aitf_env('RECAPTCHA_LOCAL_TEST_VERSION', 'v2_checkbox')
        ];

        $config = $defaults;
        $config['site_key'] = trim((string)($config['site_key'] ?? ''));
        $config['secret_key'] = trim((string)($config['secret_key'] ?? ''));
        $config['version'] = strtolower(trim((string)($config['version'] ?? 'v2_checkbox')));
        if (!in_array($config['version'], ['v2_checkbox', 'v3'], true)) {
            $config['version'] = 'v2_checkbox';
        }
        $config['action'] = trim((string)($config['action'] ?? 'submit'));
        if ($config['action'] === '') {
            $config['action'] = 'submit';
        }
        $config['minimum_score'] = (float)($config['minimum_score'] ?? 0.5);
        if ($config['minimum_score'] < 0 || $config['minimum_score'] > 1) {
            $config['minimum_score'] = 0.5;
        }
        $config['bypass_on_localhost'] = !empty($config['bypass_on_localhost']);
        $config['localhost_hosts'] = isset($config['localhost_hosts']) && is_array($config['localhost_hosts'])
            ? array_values(array_filter(array_map('strval', $config['localhost_hosts'])))
            : ['localhost', '127.0.0.1', '::1'];
        $config['theme'] = ($config['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $config['enabled'] = !empty($config['enabled']);
        $config['use_test_keys_on_localhost'] = !empty($config['use_test_keys_on_localhost']);
        $config['local_test_site_key'] = trim((string)($config['local_test_site_key'] ?? ''));
        $config['local_test_secret_key'] = trim((string)($config['local_test_secret_key'] ?? ''));
        $config['local_test_version'] = strtolower(trim((string)($config['local_test_version'] ?? 'v2_checkbox')));
        if (!in_array($config['local_test_version'], ['v2_checkbox', 'v3'], true)) {
            $config['local_test_version'] = 'v2_checkbox';
        }
        $config['using_local_test_keys'] = false;

        $requestHost = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        $requestHost = trim((string)$requestHost);
        if ($requestHost !== '' && strpos($requestHost, ':') !== false && substr_count($requestHost, ':') === 1) {
            $parts = explode(':', $requestHost, 2);
            $requestHost = $parts[0];
        }
        $requestHost = strtolower($requestHost);

        if ($config['enabled'] && $config['use_test_keys_on_localhost'] && aitf_is_local_development_host($requestHost, $config['localhost_hosts'])) {
            if ($config['local_test_site_key'] !== '' && $config['local_test_secret_key'] !== '') {
                $config['site_key'] = $config['local_test_site_key'];
                $config['secret_key'] = $config['local_test_secret_key'];
                $config['version'] = $config['local_test_version'];
                $config['bypass_on_localhost'] = false;
                $config['using_local_test_keys'] = true;
            }
        }

        return $config;
    }
}

if (!function_exists('aitf_get_request_host')) {
    function aitf_get_request_host()
    {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        $host = trim((string)$host);
        if ($host === '') {
            return '';
        }

        if (strpos($host, ':') !== false && substr_count($host, ':') === 1) {
            $parts = explode(':', $host, 2);
            $host = $parts[0];
        }

        return strtolower($host);
    }
}

if (!function_exists('aitf_is_private_or_local_ip')) {
    function aitf_is_private_or_local_ip($host)
    {
        if ($host === '') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($host);
            if ($long === false) {
                return false;
            }

            $ranges = [
                ['10.0.0.0', '10.255.255.255'],
                ['127.0.0.0', '127.255.255.255'],
                ['172.16.0.0', '172.31.255.255'],
                ['192.168.0.0', '192.168.255.255']
            ];

            foreach ($ranges as $range) {
                $start = ip2long($range[0]);
                $end = ip2long($range[1]);
                if ($start !== false && $end !== false && $long >= $start && $long <= $end) {
                    return true;
                }
            }

            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $host === '::1' || stripos($host, 'fc') === 0 || stripos($host, 'fd') === 0;
        }

        return false;
    }
}

if (!function_exists('aitf_is_local_development_host')) {
    function aitf_is_local_development_host($host, $localhostHosts = null)
    {
        if ($host === '') {
            return false;
        }

        $normalizedHost = strtolower(trim((string)$host));
        if ($localhostHosts === null) {
            $config = aitf_get_recaptcha_config();
            $localhostHosts = $config['localhost_hosts'];
        }
        $localhostHosts = array_map('strtolower', (array)$localhostHosts);

        if (in_array($normalizedHost, $localhostHosts, true)) {
            return true;
        }

        if (aitf_is_private_or_local_ip($normalizedHost)) {
            return true;
        }

        $localSuffixes = ['.local', '.test', '.localhost', '.invalid'];
        foreach ($localSuffixes as $suffix) {
            if (substr($normalizedHost, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return strpos($normalizedHost, '.') === false;
    }
}

if (!function_exists('aitf_should_bypass_recaptcha')) {
    function aitf_should_bypass_recaptcha()
    {
        $config = aitf_get_recaptcha_config();
        if (!$config['enabled'] || empty($config['bypass_on_localhost'])) {
            return false;
        }

        $host = aitf_get_request_host();
        if ($host === '') {
            return false;
        }

        return aitf_is_local_development_host($host);
    }
}

if (!function_exists('aitf_is_recaptcha_ready')) {
    function aitf_is_recaptcha_ready()
    {
        $config = aitf_get_recaptcha_config();

        return $config['enabled']
            && !aitf_should_bypass_recaptcha()
            && $config['site_key'] !== ''
            && $config['secret_key'] !== '';
    }
}

if (!function_exists('aitf_verify_recaptcha')) {
    function aitf_verify_recaptcha($token, &$errorMessage = '')
    {
        $config = aitf_get_recaptcha_config();

        if (!$config['enabled']) {
            return true;
        }

        if (aitf_should_bypass_recaptcha()) {
            return true;
        }

        if ($config['site_key'] === '' || $config['secret_key'] === '') {
            $errorMessage = 'Captcha keys are not configured yet. Please add the Google reCAPTCHA site key and secret key in the .env file.';
            return false;
        }

        $token = trim((string)$token);
        if ($token === '') {
            $errorMessage = 'Please complete the captcha verification.';
            return false;
        }

        $postFields = http_build_query([
            'secret' => $config['secret_key'],
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $responseBody = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($verifyUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            $responseBody = curl_exec($ch);
            curl_close($ch);
        }

        if ($responseBody === false) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $postFields,
                    'timeout' => 10
                ]
            ]);
            $responseBody = @file_get_contents($verifyUrl, false, $context);
        }

        if ($responseBody === false) {
            $errorMessage = 'Captcha verification failed due to a server connection issue. Please try again.';
            return false;
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded) || empty($decoded['success'])) {
            $errorCodes = [];
            if (!empty($decoded['error-codes']) && is_array($decoded['error-codes'])) {
                $errorCodes = $decoded['error-codes'];
            }
            error_log('reCAPTCHA verification failed: ' . implode(', ', $errorCodes));
            $errorMessage = 'Captcha verification failed. Please try again.';
            return false;
        }

        if ($config['version'] === 'v3') {
            $action = isset($decoded['action']) ? trim((string)$decoded['action']) : '';
            $score = isset($decoded['score']) ? (float)$decoded['score'] : null;

            if ($action !== $config['action']) {
                error_log('reCAPTCHA action mismatch: expected ' . $config['action'] . ', got ' . $action);
                $errorMessage = 'Captcha validation failed. Please refresh the page and try again.';
                return false;
            }

            if ($score === null || $score < $config['minimum_score']) {
                error_log('reCAPTCHA score too low: ' . ($score === null ? 'null' : (string)$score));
                $errorMessage = 'Captcha verification score was too low. Please try again.';
                return false;
            }
        }

        return true;
    }
}

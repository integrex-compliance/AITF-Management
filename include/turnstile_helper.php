<?php
require_once __DIR__ . '/env_helper.php';

if (!function_exists('aitf_get_turnstile_config')) {
    function aitf_get_turnstile_config()
    {
        static $config = null;

        if ($config === null) {
            $config = [
                'site_key' => trim((string)aitf_env('CLOUDFLARE_SITE_KEY', '')),
                'secret_key' => trim((string)aitf_env('CLOUDFLARE_SECRET_KEY', ''))
            ];
        }

        return $config;
    }
}

if (!function_exists('aitf_is_turnstile_ready')) {
    function aitf_is_turnstile_ready()
    {
        $config = aitf_get_turnstile_config();
        return $config['site_key'] !== '' && $config['secret_key'] !== '';
    }
}

if (!function_exists('aitf_verify_turnstile')) {
    function aitf_verify_turnstile($token, &$errorMessage = '')
    {
        $config = aitf_get_turnstile_config();

        if ($config['site_key'] === '' || $config['secret_key'] === '') {
            $errorMessage = 'Captcha keys are not configured yet. Please add the Cloudflare Turnstile site key and secret key in the .env file.';
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
        $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
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
            error_log('Turnstile verification failed: ' . implode(', ', $errorCodes));
            $errorMessage = 'Captcha verification failed. Please try again.';
            return false;
        }

        return true;
    }
}

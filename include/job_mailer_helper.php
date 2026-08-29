<?php
require_once __DIR__ . '/env_helper.php';

if (!function_exists('loadJobMailer')) {
    function loadJobMailer()
    {
        if (class_exists('PHPMailer')) {
            return true;
        }

        $candidates = [
            __DIR__ . '/../PHPMailer/PHPMailerAutoload.php',
            __DIR__ . '/../phpmailer/PHPMailerAutoload.php',
            __DIR__ . '/../PHPMailerAutoload.php'
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                require_once $path;
                return class_exists('PHPMailer');
            }
        }

        return false;
    }
}

if (!function_exists('jobMailAttempts')) {
    function jobMailAttempts()
    {
        $mailConfig = aitf_get_mail_config();

        return [
            ['host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false, 'username' => '', 'password' => ''],
            ['host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false, 'username' => '', 'password' => ''],
            [
                'host' => $mailConfig['host'],
                'port' => $mailConfig['port'],
                'secure' => $mailConfig['secure'],
                'autotls' => $mailConfig['secure'] === 'tls',
                'auth' => ($mailConfig['username'] ?? '') !== '' && ($mailConfig['password'] ?? '') !== '',
                'username' => $mailConfig['username'] ?? '',
                'password' => $mailConfig['password'] ?? ''
            ]
        ];
    }
}

if (!function_exists('configureJobMailer')) {
    function configureJobMailer(PHPMailer $mail, array $config)
    {
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'error_log';
        $mail->Host = $config['host'];
        $mail->Hostname = $config['host'];
        $mail->Port = $config['port'];
        $mail->SMTPAuth = $config['auth'];
        $mail->SMTPSecure = $config['secure'] ?: false;
        $mail->SMTPAutoTLS = $config['autotls'];
        if ($config['auth']) {
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
        }
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 8;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }
}

if (!function_exists('sendJobMail')) {
    function sendJobMail(callable $buildMail)
    {
        if (!loadJobMailer()) {
            error_log('PHPMailer autoload not found for job application mail.');
            return false;
        }

        foreach (jobMailAttempts() as $attempt) {
            try {
                $mail = new PHPMailer;
                configureJobMailer($mail, $attempt);
                $buildMail($mail);
                if ($mail->send()) {
                    return true;
                }
                error_log('Job mail SMTP fail: [' . $attempt['host'] . ':' . $attempt['port'] . '] ' . $mail->ErrorInfo);
                $mail->smtpClose();
            } catch (Exception $e) {
                error_log('Job mail exception: ' . $e->getMessage());
            }
        }

        return false;
    }
}

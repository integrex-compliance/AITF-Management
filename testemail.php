<?php

require_once __DIR__ . '/PHPMailer/PHPMailerAutoload.php';
require_once __DIR__ . '/include/env_helper.php';

$mailConfig = aitf_get_mail_config([
    'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited')
]);

class AITFServiceEmailSender {

    /**
     * Build connection attempts for high deliverability across localhost and live hosting (GoDaddy, cPanel, VPS)
     */
    private function getMailerAttempts($mailConfig) {
        $attempts = [];

        // 1. Configured SMTP from .env (e.g. smtpout.secureserver.net:587 TLS)
        if (!empty($mailConfig['host'])) {
            $attempts[] = [
                'type' => 'smtp',
                'host' => $mailConfig['host'],
                'port' => (int)($mailConfig['port'] ?: 587),
                'secure' => $mailConfig['secure'] ?: 'tls',
                'autotls' => ($mailConfig['secure'] === 'tls'),
                'auth' => !empty($mailConfig['username']) && !empty($mailConfig['password']),
                'timeout' => 8
            ];

            // If port is not 465, also add port 465 with SSL as secondary attempt
            if ((int)$mailConfig['port'] !== 465) {
                $attempts[] = [
                    'type' => 'smtp',
                    'host' => $mailConfig['host'],
                    'port' => 465,
                    'secure' => 'ssl',
                    'autotls' => false,
                    'auth' => !empty($mailConfig['username']) && !empty($mailConfig['password']),
                    'timeout' => 8
                ];
            }
        }

        // 2. GoDaddy / cPanel internal relays (relay without authentication inside host network)
        $attempts[] = [
            'type' => 'smtp',
            'host' => 'localhost',
            'port' => 25,
            'secure' => '',
            'autotls' => false,
            'auth' => false,
            'timeout' => 4
        ];
        $attempts[] = [
            'type' => 'smtp',
            'host' => 'relay-hosting.secureserver.net',
            'port' => 25,
            'secure' => '',
            'autotls' => false,
            'auth' => false,
            'timeout' => 4
        ];

        // 3. Fallback to PHP native mail() if hosting firewall blocks all outbound SMTP sockets
        $attempts[] = [
            'type' => 'mail'
        ];

        return $attempts;
    }

    private function configureMailer(PHPMailer $mail, array $cfg, array $mailConfig) {
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        if ($cfg['type'] === 'smtp') {
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'error_log';
            $mail->Host = $cfg['host'];
            $mail->Hostname = $cfg['host'];
            $mail->Port = $cfg['port'];
            $mail->SMTPSecure = $cfg['secure'] ?: false;
            $mail->SMTPAutoTLS = $cfg['autotls'] ?? false;
            $mail->Timeout = $cfg['timeout'] ?? 8;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            if (!empty($cfg['auth'])) {
                $mail->SMTPAuth = true;
                $mail->Username = $mailConfig['username'];
                $mail->Password = $mailConfig['password'];
            } else {
                $mail->SMTPAuth = false;
            }
        } else {
            $mail->IsMail();
        }

        $mail->From = $mailConfig['from_email'] ?: $mailConfig['username'];
        $mail->FromName = $mailConfig['from_name'] ?: 'AITF Management Certification Limited';
        $mail->Sender = $mailConfig['from_email'] ?: $mailConfig['username'];
        $mail->addCustomHeader('Return-Path', $mailConfig['from_email'] ?: $mailConfig['username']);
    }

    function sendEmail($toEmails, $toName, $body, $subject) {
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited')
        ]);
        $attempts = $this->getMailerAttempts($mailConfig);
        $lastError = '';

        foreach ($attempts as $attempt) {
            $mail = new PHPMailer(true);
            try {
                $this->configureMailer($mail, $attempt, $mailConfig);

                if (is_array($toEmails)) {
                    foreach ($toEmails as $em) {
                        $mail->AddAddress(trim($em), $toName);
                    }
                } else {
                    $mail->AddAddress(trim($toEmails), $toName);
                }

                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->IsHTML(true);

                $mail->Send();
                return true;
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo ?: $e->getMessage();
                error_log("AITFServiceEmailSender sendEmail fail [{$attempt['type']}]: " . $lastError);
                if ($mail->Mailer === 'smtp') {
                    @$mail->smtpClose();
                }
                continue;
            }
        }

        throw new Exception($lastError ?: 'Failed to send email through all available methods.');
    }

    function sendAttachEmail($toEmails, $toName, $body, $subject, $attachment) {
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited')
        ]);
        $attempts = $this->getMailerAttempts($mailConfig);
        $lastError = '';

        foreach ($attempts as $attempt) {
            $mail = new PHPMailer(true);
            try {
                $this->configureMailer($mail, $attempt, $mailConfig);

                if (is_array($toEmails)) {
                    foreach ($toEmails as $em) {
                        $mail->AddAddress(trim($em), $toName);
                    }
                } else {
                    $mail->AddAddress(trim($toEmails), $toName);
                }

                $mail->Subject = $subject;
                $mail->Body = $body;
                
                if (!empty($attachment) && file_exists($attachment)) {
                    $mail->AddAttachment($attachment, '');
                }

                $mail->IsHTML(true);

                $mail->Send();
                return true;
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo ?: $e->getMessage();
                error_log("AITFServiceEmailSender sendAttachEmail fail [{$attempt['type']}]: " . $lastError);
                if ($mail->Mailer === 'smtp') {
                    @$mail->smtpClose();
                }
                continue;
            }
        }

        throw new Exception($lastError ?: 'Failed to send attachment email.');
    }

    function sendMultipleAttachmentsEmail($toEmails, $toName, $body, $subject, $attachments, $ccEmails = null) {
        $mailConfig = aitf_get_mail_config([
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited')
        ]);
        $attempts = $this->getMailerAttempts($mailConfig);
        $lastError = '';

        foreach ($attempts as $attempt) {
            $mail = new PHPMailer(true);
            try {
                $this->configureMailer($mail, $attempt, $mailConfig);

                if (is_array($toEmails)) {
                    foreach ($toEmails as $em) {
                        $mail->AddAddress(trim($em), $toName);
                    }
                } else {
                    $mail->AddAddress(trim($toEmails), $toName);
                }

                if (!empty($ccEmails)) {
                    if (is_array($ccEmails)) {
                        foreach ($ccEmails as $cc) {
                            if (!empty($cc)) $mail->addCC(trim($cc));
                        }
                    } else {
                        $mail->addCC(trim($ccEmails));
                    }
                }

                $mail->Subject = $subject;
                $mail->Body = $body;
                
                if (is_array($attachments)) {
                    foreach ($attachments as $file_to_attach) {
                        if (!empty($file_to_attach) && file_exists($file_to_attach)) {
                            $mail->AddAttachment($file_to_attach, '');
                        }
                    }
                }

                $mail->IsHTML(true);

                $mail->Send();
                return true;
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo ?: $e->getMessage();
                error_log("AITFServiceEmailSender sendMultipleAttachmentsEmail fail [{$attempt['type']}]: " . $lastError);
                if ($mail->Mailer === 'smtp') {
                    @$mail->smtpClose();
                }
                continue;
            }
        }

        throw new Exception($lastError ?: 'Failed to send attachments email.');
    }

    function sendQueryEmail($toEmails, $toName, $body, $subject, $fromName, $ismultiple) {
        $mailConfig = aitf_get_mail_config([
            'from_name' => $fromName ?: aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited')
        ]);
        $attempts = $this->getMailerAttempts($mailConfig);
        $lastError = '';

        foreach ($attempts as $attempt) {
            $mail = new PHPMailer(true);
            try {
                $this->configureMailer($mail, $attempt, $mailConfig);

                if ($ismultiple) {
                    $mail->AddAddress("recruitgunadhya@gmail.com", "Challenge");
                    if (is_array($toEmails)) {
                        foreach ($toEmails as $toEmail) {
                            $mail->addBCC(trim($toEmail), trim($toEmail));
                        }
                    }
                } else {
                    if (is_array($toEmails)) {
                        foreach ($toEmails as $toEmail) {
                            $mail->AddAddress(trim($toEmail), $toName);
                        }
                    } else {
                        $mail->AddAddress(trim($toEmails), $toName);
                    }
                }

                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->IsHTML(true);

                $mail->Send();
                return true;
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo ?: $e->getMessage();
                error_log("AITFServiceEmailSender sendQueryEmail fail [{$attempt['type']}]: " . $lastError);
                if ($mail->Mailer === 'smtp') {
                    @$mail->smtpClose();
                }
                continue;
            }
        }

        throw new Exception($lastError ?: 'Failed to send query email.');
    }
}

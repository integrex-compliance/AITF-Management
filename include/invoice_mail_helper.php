<?php
require_once __DIR__ . '/env_helper.php';

if (!function_exists('aitfLoadPHPMailer')) {
    function aitfLoadPHPMailer() {
        static $loaded = false;

        if ($loaded || class_exists('PHPMailer')) {
            $loaded = true;
            return;
        }

        

        $candidates = array(
            __DIR__ . '/../PHPMailer/PHPMailerAutoload.php',
            __DIR__ . '/../phpmailer/PHPMailerAutoload.php',
            __DIR__ . '/../PHPMailerAutoload.php'
        );

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                require_once $path;
                $loaded = true;
                return;
            }
        }
          

        throw new Exception('PHPMailer autoload not found');
    }

    function aitfGetInvoiceMailConfig() {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $config = aitf_get_mail_config(array(
            'from_name' => aitf_env('MAIL_FROM_NAME', 'AITF Management Certification Limited'),
            'reply_to_name' => aitf_env('MAIL_REPLY_TO_NAME', 'AITF Management Certification Limited')
        ));

        if (empty($config['reply_to_email'])) {
            $config['reply_to_email'] = $config['from_email'];
        }

        if (empty($config['reply_to_name'])) {
            $config['reply_to_name'] = $config['from_name'];
        }

        if (empty($config['office_copy'])) {
            $config['office_copy'] = $config['username'];
        }

        return $config;
    }

    function aitfGetInvoiceMailAttempts() {
        $mailConfig = aitfGetInvoiceMailConfig();

        return array(
            array('host' => $mailConfig['host'], 'port' => $mailConfig['port'], 'secure' => $mailConfig['secure'], 'autotls' => true, 'auth' => true),
            array('host' => 'localhost', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false),
            array('host' => 'relay-hosting.secureserver.net', 'port' => 25, 'secure' => '', 'autotls' => false, 'auth' => false)
        );
    }

    function aitfApplyInvoiceMailerConfig($mailer, $attempt, $mailConfig, $fromEmail, $fromName) {
        $mailer->isSMTP();
        $mailer->SMTPDebug = 0;
        $mailer->Debugoutput = 'error_log';
        $mailer->Host = $attempt['host'];
        $mailer->Hostname = $attempt['host'];
        $mailer->Port = $attempt['port'];
        $mailer->SMTPAuth = $attempt['auth'];
        $mailer->SMTPSecure = $attempt['secure'] ? $attempt['secure'] : false;
        $mailer->SMTPAutoTLS = $attempt['autotls'];
        if ($attempt['auth']) {
            $mailer->Username = $mailConfig['username'];
            $mailer->Password = $mailConfig['password'];
        }
        $mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mailer->CharSet = 'UTF-8';
        $mailer->Timeout = 10;
        $mailer->SMTPKeepAlive = false;
        $mailer->From = $fromEmail;
        $mailer->FromName = $fromName;
        $mailer->Sender = $fromEmail;
        if (method_exists($mailer, 'ClearReplyTos')) {
            $mailer->ClearReplyTos();
        }
        $mailer->addReplyTo($mailConfig['reply_to_email'], $mailConfig['reply_to_name']);
    }

    function aitfAddEmailRecipients($mailer, $emails) {
        $recipients = preg_split('/[,;]+/', (string) $emails);
        $added = array();

        foreach ($recipients as $email) {
            $email = trim($email);
            if ($email === '' || isset($added[strtolower($email)])) {
                continue;
            }
            $mailer->AddAddress($email);
            $added[strtolower($email)] = true;
        }

        return count($added);
    }

    function aitfSendInvoiceMailer($mailer) {
        $mailConfig = aitfGetInvoiceMailConfig();
        $attempts = aitfGetInvoiceMailAttempts();
        $senderOptions = array(
            array($mailConfig['from_email'], $mailConfig['from_name']),
            array($mailConfig['username'], $mailConfig['from_name'])
        );
        $errors = array();

        foreach ($senderOptions as $senderOption) {
            $fromEmail = $senderOption[0];
            $fromName = $senderOption[1];

            foreach ($attempts as $attempt) {
                aitfApplyInvoiceMailerConfig($mailer, $attempt, $mailConfig, $fromEmail, $fromName);
                $sent = $mailer->send();
                if ($sent) {
                    return array(true, '');
                }

                $errors[] = '[' . $fromEmail . ' via ' . $attempt['host'] . ':' . $attempt['port'] . ' secure=' . ($attempt['secure'] ? $attempt['secure'] : 'none') . ' auth=' . ($attempt['auth'] ? 'yes' : 'no') . '] ' . $mailer->ErrorInfo;
                error_log('INVOICE MAIL FAIL: ' . end($errors));
                $mailer->smtpClose();
            }
        }

        return array(false, implode(' | ', $errors));
    }

    function aitfInvoiceEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function aitfInvoiceFormatDate($dateValue) {
        if (empty($dateValue)) {
            return '';
        }

        $timestamp = strtotime($dateValue);
        return $timestamp ? date('d.m.Y', $timestamp) : (string) $dateValue;
    }

    function aitfInvoiceFormatMoney($amount) {
        return number_format((float) $amount, 2);
    }

    function aitfInvoiceServiceNames($invoiceSvc) {
        $services = array();

        foreach ($invoiceSvc as $service) {
            if (!empty($service['service_desc'])) {
                $services[] = trim($service['service_desc']);
            }
        }

        $services = array_unique(array_filter($services));
        return empty($services) ? 'Certification Services' : implode(', ', $services);
    }

    function aitfBuildInvoiceTaxLines($invoice, $htmlList) {
        $taxType = isset($invoice['tax_type']) ? $invoice['tax_type'] : '';
        $lines = array();

        if ($taxType === 'CGST & SGST') {
            $lines[] = array('CGST @9%', isset($invoice['cgst']) ? $invoice['cgst'] : 0);
            $lines[] = array('SGST @9%', isset($invoice['sgst']) ? $invoice['sgst'] : 0);
        } elseif ($taxType === 'IGST') {
            $lines[] = array('IGST @18%', isset($invoice['igst']) ? $invoice['igst'] : 0);
        }

        $output = '';
        foreach ($lines as $line) {
            $label = aitfInvoiceEscape($line[0]);
            $amount = '&#8377;' . aitfInvoiceFormatMoney($line[1]);
            if ($htmlList) {
                $output .= '<li>' . $label . ': ' . $amount . '</li>';
            } else {
                $output .= $label . ': ' . html_entity_decode($amount, ENT_QUOTES, 'UTF-8') . "\n";
            }
        }

        return $output;
    }

    function aitfBuildProformaMailBody($customerInfo, $invoice, $invoiceSvc) {
        $invoiceDate = aitfInvoiceFormatDate(isset($invoice['invoice_date']) ? $invoice['invoice_date'] : '');
        $serviceNames = aitfInvoiceEscape(aitfInvoiceServiceNames($invoiceSvc));
        $netAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['net_price']) ? $invoice['net_price'] : 0);
        $totalAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['final_amount']) ? $invoice['final_amount'] : 0);
        $taxLines = aitfBuildInvoiceTaxLines($invoice, true);

        $body = '<html><body>'
            . '<p>Dear Sir/Madam,</p>'
            . '<p>Greetings from AITF Management Certification Limited.</p>'
            . '<p>Please note that <strong>this is a Proforma Invoice</strong> for the certification services. Kindly find the attached Proforma Invoice dated ' . aitfInvoiceEscape($invoiceDate) . ' for your reference.</p>'
            . '<p>This Proforma Invoice is issued for <strong>payment intimation and processing purposes only</strong>. <strong>No tax credit should be claimed against this Proforma Invoice.</strong> The final tax invoice will be issued only after confirmation of payment realization.</p>'
            . '<p><strong>Invoice Summary:</strong></p>'
            . '<ul>'
            . '<li>Service: ' . $serviceNames . '</li>'
            . '<li>Net Amount: ' . $netAmount . '</li>'
            . $taxLines
            . '<li><strong>Total Amount Payable: ' . $totalAmount . '</strong></li>'
            . '</ul>'
            . '<p>We request you to kindly process the payment as per the details mentioned in the invoice.</p>'
            . '<p>If you need any clarification, please feel free to contact us.</p>'
            . '<p>Thank you for your cooperation.</p>'
            . '<p>Warm regards,<br>'
            . 'AITF Management Certification Limited<br>'
            . 'Email: <a href="mailto:aitfacct@gmail.com">aitfacct@gmail.com</a><br>'
            . 'Website: <a href="http://www.aitf.co.in">www.aitf.co.in</a></p>'
            . '</body></html>';

        return $body;
    }

    function aitfBuildFinalInvoiceMailBody($customerInfo, $invoice, $invoiceSvc, $customMessage) {
        $invoiceDate = aitfInvoiceFormatDate(isset($invoice['invoice_date']) ? $invoice['invoice_date'] : '');
        $clientName = isset($customerInfo['customer_name']) && trim((string) $customerInfo['customer_name']) !== '' ? trim((string) $customerInfo['customer_name']) : 'Client';
        $invoiceNo = '';
        if (!empty($invoice['final_prefix']) && !empty($invoice['final_invoice_no'])) {
            $invoiceNo = trim((string) $invoice['final_prefix']) . '-' . trim((string) $invoice['final_invoice_no']);
        } elseif (!empty($invoice['profarma_prefix']) && !empty($invoice['invoice_no'])) {
            $invoiceNo = trim((string) $invoice['profarma_prefix']) . '-' . trim((string) $invoice['invoice_no']);
        } elseif (!empty($invoice['invoice_no'])) {
            $invoiceNo = trim((string) $invoice['invoice_no']);
        }
        $serviceNames = aitfInvoiceEscape(aitfInvoiceServiceNames($invoiceSvc));
        $taxableAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['net_price']) ? $invoice['net_price'] : 0);
        $totalAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['final_amount']) ? $invoice['final_amount'] : 0);
        $customBlock = trim((string) $customMessage) !== '' ? '<p>' . nl2br(aitfInvoiceEscape($customMessage)) . '</p>' : '';

        $taxType = isset($invoice['tax_type']) ? (string) $invoice['tax_type'] : '';
        $cgstAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['cgst']) ? $invoice['cgst'] : 0);
        $sgstAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['sgst']) ? $invoice['sgst'] : 0);
        $igstAmount = '&#8377;' . aitfInvoiceFormatMoney(isset($invoice['igst']) ? $invoice['igst'] : 0);
        $taxSummaryLines = '';
        if ($taxType === 'IGST') {
            $taxSummaryLines .= '<li>IGST @18%: ' . $igstAmount . '</li>';
            $taxSummaryLines .= '<li>CGST @9%: ' . $cgstAmount . '</li>';
            $taxSummaryLines .= '<li>SGST @9%: ' . $sgstAmount . '</li>';
        } else {
            $taxSummaryLines .= '<li>CGST @9%: ' . $cgstAmount . '</li>';
            $taxSummaryLines .= '<li>SGST @9%: ' . $sgstAmount . '</li>';
        }

        return '<html><body>'
            . '<p>Dear ' . aitfInvoiceEscape($clientName) . ',</p>'
            . '<p>Greetings from AITF Management Certification Limited.</p>'
            . '<p>Please find attached the <strong>Final Tax Invoice</strong> for the services provided.</p>'
            . '<p><strong>Invoice Details:</strong></p>'
            . '<ul>'
            . '<li>Invoice No: ' . aitfInvoiceEscape($invoiceNo) . '</li>'
            . '<li>Invoice Date: ' . aitfInvoiceEscape($invoiceDate) . '</li>'
            . '<li>Service: ' . $serviceNames . '</li>'
            . '</ul>'
            . '<p><strong>Invoice Summary:</strong></p>'
            . '<ul>'
            . '<li>Taxable Amount: ' . $taxableAmount . '</li>'
            . $taxSummaryLines
            . '<li><strong>Total Amount Payable: ' . $totalAmount . '</strong></li>'
            . '</ul>'
            . '<p>We request you to kindly process the payment at your earliest convenience as per the bank details mentioned in the invoice.</p>'
            . '<p>If you have any questions or require further clarification, please feel free to contact us.</p>'
            . '<p>Thank you for your continued support.</p>'
            . $customBlock
            . '<p>Warm regards,<br>'
            . 'AITF Management Certification Limited<br>'
            . 'Email: <a href="mailto:aitfacct@gmail.com">aitfacct@gmail.com</a><br>'
            . 'Website: <a href="http://www.aitf.co.in">www.aitf.co.in</a></p>'
            . '</body></html>';
    }

    function aitfBuildInvoiceRedirectUrl($success, $message) {
        if ($success) {
            return 'invoicePage.php?mailsend=1';
        }

        return 'invoicePage.php?mailsend=0&mailerror=' . urlencode($message);
    }
}

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

    function aitfBuildPendingPaymentReminderMailBody($clientName, $invoiceNumber, $invoiceDate, $invoiceType, $totalAmount, $paidAmount, $pendingAmount, $dueDate, $isHtml = true) {
        $clientName = trim((string) $clientName) !== '' ? trim((string) $clientName) : 'Valued Client';
        $invoiceNumber = trim((string) $invoiceNumber);
        $invoiceDate = trim((string) $invoiceDate);
        $invoiceType = trim((string) $invoiceType);
        $totalAmount = is_numeric(str_replace(',', '', (string) $totalAmount)) ? number_format((float) str_replace(',', '', (string) $totalAmount), 2) : (string) $totalAmount;
        $paidAmount = is_numeric(str_replace(',', '', (string) $paidAmount)) ? number_format((float) str_replace(',', '', (string) $paidAmount), 2) : (string) $paidAmount;
        $pendingAmount = is_numeric(str_replace(',', '', (string) $pendingAmount)) ? number_format((float) str_replace(',', '', (string) $pendingAmount), 2) : (string) $pendingAmount;
        $dueDate = trim((string) $dueDate);

        if (!$isHtml) {
            return "Dear {$clientName},\n\n"
                . "Greetings from  A.I.T.F. (Assessment And Institute for Technical Forum).\n\n"
                . "We hope you are doing well.\n\n"
                . "This is a gentle reminder regarding the pending payment against the invoice issued by AITF MANAGEMENT CERTIFICATION LIMITED.\n\n"
                . "Please find the invoice details below for your reference:\n\n"
                . " Invoice Details:\n"
                . " Invoice Number: {$invoiceNumber}\n"
                . " Invoice Date: {$invoiceDate}\n"
                . " Invoice Type: {$invoiceType}\n"
                . " Total Invoice Amount: ₹{$totalAmount}\n"
                . " Amount Paid: ₹{$paidAmount}\n"
                . " Outstanding Amount: ₹{$pendingAmount}\n"
                . " Payment Due Date: {$dueDate}\n\n"
                . "As per our records, the outstanding amount of ₹{$pendingAmount} is still pending against the above-mentioned invoice.\n\n"
                . "We kindly request you to arrange the payment at your earliest convenience. If the payment has already been processed, please share the payment confirmation or transaction details so that we can update our records accordingly.\n\n"
                . "If you have any queries regarding the invoice or require any clarification regarding the outstanding amount, please feel free to contact us.\n\n"
                . "We sincerely appreciate your association with AITF MANAGEMENT CERTIFICATION LIMITED and look forward to your prompt response.\n\n"
                . "Thank you for your time and cooperation.\n\n"
                . "Best Regards,\n"
                . "Accounts & Finance Team\n"
                . "AITF MANAGEMENT CERTIFICATION LIMITED\n"
                . "Phone: +91 9222 1836\n"
                . "Email: aitfacct@gmail.com\n"
                . "Website: www.aitf.co.in";
        }

        $escClientName = aitfInvoiceEscape($clientName);
        $escInvoiceNumber = aitfInvoiceEscape($invoiceNumber);
        $escInvoiceDate = aitfInvoiceEscape($invoiceDate);
        $escInvoiceType = aitfInvoiceEscape($invoiceType);
        $escTotalAmount = aitfInvoiceEscape($totalAmount);
        $escPaidAmount = aitfInvoiceEscape($paidAmount);
        $escPendingAmount = aitfInvoiceEscape($pendingAmount);
        $escDueDate = aitfInvoiceEscape($dueDate);

        return '<html><head><meta charset="utf-8">'
            . '<style>'
            . 'body { font-family: "Segoe UI", Arial, sans-serif; font-size: 14.5px; line-height: 1.65; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }'
            . '.mail-card { max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); }'
            . '.header-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }'
            . '.details-card { background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #0d4d8b; border-radius: 8px; padding: 18px 22px; margin: 20px 0; }'
            . '.details-title { font-size: 15px; font-weight: 700; color: #0d4d8b; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }'
            . '.details-table { width: 100%; border-collapse: collapse; font-size: 14px; }'
            . '.details-table td { padding: 7px 4px; vertical-align: top; }'
            . '.details-table td.lbl { width: 44%; color: #64748b; font-weight: 600; }'
            . '.details-table td.val { width: 56%; color: #0f172a; font-weight: 700; }'
            . '.outstanding-highlight td { color: #dc2626 !important; font-size: 15px !important; font-weight: 800 !important; }'
            . '.footer-section { margin-top: 28px; padding-top: 20px; border-top: 2px solid #e2e8f0; font-size: 13.5px; color: #475569; }'
            . '</style></head>'
            . '<body>'
            . '<div class="mail-card">'
            . '<div class="header-badge">Payment Reminder</div>'
            . '<p>Dear <strong>' . $escClientName . '</strong>,</p>'
            . '<p>Greetings from &nbsp;<strong>A.I.T.F. (Assessment And Institute for Technical Forum)</strong>.</p>'
            . '<p>We hope you are doing well.</p>'
            . '<p>This is a gentle reminder regarding the pending payment against the invoice issued by <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong>.</p>'
            . '<p>Please find the invoice details below for your reference:</p>'
            . '<div class="details-card">'
            . '<div class="details-title">Invoice Details:</div>'
            . '<table class="details-table">'
            . '<tr><td class="lbl">Invoice Number:</td><td class="val">' . $escInvoiceNumber . '</td></tr>'
            . '<tr><td class="lbl">Invoice Date:</td><td class="val">' . $escInvoiceDate . '</td></tr>'
            . '<tr><td class="lbl">Invoice Type:</td><td class="val">' . $escInvoiceType . '</td></tr>'
            . '<tr><td class="lbl">Total Invoice Amount:</td><td class="val">&#8377;' . $escTotalAmount . '</td></tr>'
            . '<tr><td class="lbl">Amount Paid:</td><td class="val">&#8377;' . $escPaidAmount . '</td></tr>'
            . '<tr class="outstanding-highlight"><td class="lbl" style="color: #dc2626;">Outstanding Amount:</td><td class="val" style="color: #dc2626;">&#8377;' . $escPendingAmount . '</td></tr>'
            . '<tr><td class="lbl">Payment Due Date:</td><td class="val">' . $escDueDate . '</td></tr>'
            . '</table>'
            . '</div>'
            . '<p>As per our records, the outstanding amount of <strong style="color: #dc2626;">&#8377;' . $escPendingAmount . '</strong> is still pending against the above-mentioned invoice.</p>'
            . '<p>We kindly request you to arrange the payment at your earliest convenience. If the payment has already been processed, please share the payment confirmation or transaction details so that we can update our records accordingly.</p>'
            . '<p>If you have any queries regarding the invoice or require any clarification regarding the outstanding amount, please feel free to contact us.</p>'
            . '<p>We sincerely appreciate your association with <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong> and look forward to your prompt response.</p>'
            . '<p>Thank you for your time and cooperation.</p>'
            . '<div class="footer-section">'
            . '<p style="margin:0 0 4px 0;"><strong>Best Regards,</strong></p>'
            . '<p style="margin:0 0 2px 0;">Accounts &amp; Finance Team</p>'
            . '<p style="margin:0 0 8px 0; font-weight: 700; color: #0d4d8b;">AITF MANAGEMENT CERTIFICATION LIMITED</p>'
            . '<p style="margin:0 0 3px 0;">Phone: +91 9222 1836</p>'
            . '<p style="margin:0 0 3px 0;">Email: <a href="mailto:aitfacct@gmail.com" style="color: #0d4d8b; text-decoration: none;">aitfacct@gmail.com</a></p>'
            . '<p style="margin:0;">Website: <a href="http://www.aitf.co.in" style="color: #0d4d8b; text-decoration: none;">www.aitf.co.in</a></p>'
            . '</div>'
            . '</div>'
            . '</body></html>';
    }
}

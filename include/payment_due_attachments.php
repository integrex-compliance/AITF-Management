<?php
/**
 * payment_due_attachments.php
 * 
 * Helper library to fetch service attachments (invoices + uploaded service documents)
 * and generate final invoice PDF attachments for payment reminder emails.
 */

require_once __DIR__ . '/payment_due_helper.php';
require_once __DIR__ . '/../tcpdf_include.php';

if (!function_exists('aitf_format_file_size')) {
    function aitf_format_file_size($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

if (!function_exists('aitf_sanitize_attachment_filename')) {
    function aitf_sanitize_attachment_filename($name) {
        $clean = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $name);
        return preg_replace('/_+/', '_', $clean);
    }
}

/**
 * Fetch all available attachments for a given customer and invoice header (service).
 * Returns array of:
 * - Invoices belonging to that service header
 * - Any uploaded files in service_type_* that exist on the server
 */
if (!function_exists('aitf_get_service_attachments')) {
    function aitf_get_service_attachments(PDO $db, $customerId, $headerKey) {
        $customerId = (int)$customerId;
        $headerKey = trim((string)$headerKey);
        $attachments = [];

        if ($customerId <= 0) {
            return $attachments;
        }

        // 1. Fetch Invoices for this customer & header
        $allDues = aitf_get_due_payments($db, ['customerId' => $customerId, 'headerKey' => $headerKey]);
        $clientDue = $allDues[0] ?? null;

        if ($clientDue && !empty($clientDue['invoices'])) {
            foreach ($clientDue['invoices'] as $inv) {
                $invId = (int)$inv['id'];
                $invNum = trim((string)($inv['number'] ?? ''));
                if ($invNum === '') {
                    $invNum = 'Invoice #' . $invId;
                }
                $cleanNum = aitf_sanitize_attachment_filename($invNum);
                $fileName = 'Invoice-' . $cleanNum . '.pdf';
                $amtFmt = number_format((float)($inv['amount'] ?? 0), 2);

                $attachments[] = [
                    'id' => 'inv_' . $invId,
                    'type' => 'invoice',
                    'invoice_id' => $invId,
                    'name' => $fileName,
                    'label' => $invNum . ' (Invoice PDF)',
                    'sublabel' => 'Amount: ₹' . $amtFmt,
                    'badge' => 'Service Invoice',
                    'badge_class' => 'badge-invoice'
                ];
            }
        }

        // 2. Also check if there are any uploaded service files for this customer in service_type_*
        $baseDir = realpath(__DIR__ . '/..');
        $serviceTableMap = [
            'service_type_ca' => ['application_form' => 'Application Form', 'certification_aggrement' => 'Agreement', 'signed_notification' => 'Signed Notification', 'signed_copy' => 'Signed Copy', 'audit_report_I' => 'Audit Report I', 'signed_notification_II' => 'Signed Noti II', 'audit_report_II' => 'Audit Report II', 'certificate_print' => 'Certificate'],
            'service_type_cr' => ['application_form' => 'Application Form', 'certification_aggrement' => 'Agreement'],
            'service_type_rc' => ['rc_application_form' => 'RC Application', 'rc_certification_aggrement' => 'RC Agreement', 'rc_signed_noti' => 'RC Signed Noti', 'rc_signed_copy' => 'RC Signed Copy', 'rc_audit_report' => 'RC Audit Report', 'certificate_print' => 'Certificate'],
            'service_type_sai' => ['info_update_form' => 'Info Update Form', 'signed_noti_sai' => 'SA-I Signed Noti', 'signed_copy_sai' => 'SA-I Signed Copy', 'audit_report_sai' => 'SA-I Audit Report', 'certificate_print' => 'Certificate'],
            'service_type_saii' => ['update_info_saii' => 'Info Update Form', 'signed_noti_saii' => 'SA-II Signed Noti', 'signed_copy_saii' => 'SA-II Signed Copy', 'audit_report_saii' => 'SA-II Audit Report', 'certificate_print' => 'Certificate'],
            'service_type_sp' => ['sp_signed_noti' => 'SP Signed Noti', 'sp_signed_copy' => 'SP Signed Copy', 'sp_audit_report' => 'SP Audit Report', 'certificate_upload' => 'Certificate'],
            'service_type_ta' => ['signed_notification' => 'TA Notification', 'release_schedule' => 'Release Schedule', 'audit_report' => 'TA Audit Report', 'review_report' => 'Review Report']
        ];

        // Determine if headerKey maps to specific service types
        $hkUpper = strtoupper($headerKey);
        $tablesToCheck = [];
        if (strpos($hkUpper, 'CA') !== false) {
            $tablesToCheck[] = 'service_type_ca';
        } elseif (strpos($hkUpper, 'RC') !== false) {
            $tablesToCheck[] = 'service_type_rc';
        } elseif (strpos($hkUpper, 'SA-I') !== false || strpos($hkUpper, 'SAI') !== false) {
            $tablesToCheck[] = 'service_type_sai';
        } elseif (strpos($hkUpper, 'SA-II') !== false || strpos($hkUpper, 'SAII') !== false) {
            $tablesToCheck[] = 'service_type_saii';
        } elseif (strpos($hkUpper, 'CR') !== false) {
            $tablesToCheck[] = 'service_type_cr';
        } elseif (strpos($hkUpper, 'SP') !== false) {
            $tablesToCheck[] = 'service_type_sp';
        } elseif (strpos($hkUpper, 'TA') !== false) {
            $tablesToCheck[] = 'service_type_ta';
        } else {
            // Check all relevant service tables
            $tablesToCheck = array_keys($serviceTableMap);
        }

        foreach ($tablesToCheck as $tbl) {
            $fields = $serviceTableMap[$tbl] ?? [];
            if (empty($fields)) continue;

            $custCol = in_array($tbl, ['service_type_rc', 'service_type_sp']) ? ($tbl === 'service_type_rc' ? 'rc_customer' : 'sp_customer') : 'customer_id';
            try {
                $stmt = $db->prepare("SELECT * FROM `{$tbl}` WHERE `{$custCol}` = :cid ORDER BY 1 DESC LIMIT 3");
                $stmt->execute([':cid' => $customerId]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    foreach ($fields as $colName => $colLabel) {
                        $rawPath = trim((string)($row[$colName] ?? ''));
                        if ($rawPath === '' || $rawPath === 'NONC' || strtolower($rawPath) === 'null') {
                            continue;
                        }
                        $cleanRel = ltrim(str_replace('\\', '/', $rawPath), '/');
                        $fullPath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanRel);
                        if (file_exists($fullPath) && is_file($fullPath)) {
                            $fSize = filesize($fullPath);
                            $bName = basename($cleanRel);
                            $attachments[] = [
                                'id' => 'doc_' . md5($cleanRel),
                                'type' => 'service_doc',
                                'doc_path' => $cleanRel,
                                'name' => $bName,
                                'label' => $colLabel . ' (' . $bName . ')',
                                'sublabel' => aitf_format_file_size($fSize),
                                'badge' => 'Service Doc',
                                'badge_class' => 'badge-service-doc'
                            ];
                        }
                    }
                }
            } catch (Throwable $e) {}
        }

        return $attachments;
    }
}

/**
 * Generate official Final Invoice PDF file to a designated path on disk.
 */
if (!function_exists('aitf_generate_final_invoice_pdf')) {
    function aitf_generate_final_invoice_pdf(PDO $db, $invoiceId, $outputPath) {
        $invoiceId = (int)$invoiceId;
        if ($invoiceId <= 0) {
            return false;
        }

        $stmt = $db->prepare("SELECT * FROM invoice_details WHERE invoice_id = :id AND (is_deleted = 'No' OR is_deleted IS NULL)");
        $stmt->execute([':id' => $invoiceId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) {
            return false;
        }

        $customerId = (int)$invoice['customer_id'];
        $stmtCust = $db->prepare("SELECT * FROM customer_info WHERE id = :id");
        $stmtCust->execute([':id' => $customerId]);
        $customerInfo = $stmtCust->fetch(PDO::FETCH_ASSOC);

        $stmtSvc = $db->prepare("SELECT * FROM invoice_services WHERE invoice_ids = :id");
        $stmtSvc->execute([':id' => $invoiceId]);
        $invoiceSvc = $stmtSvc->fetchAll(PDO::FETCH_ASSOC);

        $stmtFirm = $db->query("SELECT * FROM consulting_firm LIMIT 1");
        $firmInfo = $stmtFirm ? $stmtFirm->fetch(PDO::FETCH_ASSOC) : [];

        $firmSign = $firmInfo['signature'] ?? '';
        $signaturePath = '';
        if (!empty($firmSign)) {
            $baseDir = realpath(__DIR__ . '/..');
            $candidate = $baseDir . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $firmSign), DIRECTORY_SEPARATOR);
            if (file_exists($candidate)) {
                $signaturePath = str_replace('\\', '/', $candidate);
            }
        }

        $invno = $invoice['final_invoice_no'];
        $invDate = $invoice['invoice_date'];
        $netPrice = (float)($invoice['net_price'] ?? 0);
        $agreementNo = trim((string)($invoice['agreement_no'] ?? ''));
        $poRef = trim((string)($invoice['po_ref'] ?? ''));
        $total_amt = (float)($invoice['final_amount'] ?? 0);
        $state = $invoice['state'] ?? '';
        $stateCode = $invoice['state_code'] ?? '';
        $finalprefix = trim((string)($invoice['final_prefix'] ?? ''));

        // If agreement_no is empty, check customer unique_id / certification_aggrement
        if ($agreementNo === '' && !empty($customerId)) {
            try {
                $stmtAgr = $db->prepare("SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :cid AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1");
                $stmtAgr->execute([':cid' => $customerId]);
                $agrRow = $stmtAgr->fetch(PDO::FETCH_ASSOC);
                if (!empty($agrRow['aggrement_no'])) {
                    $agreementNo = trim($agrRow['aggrement_no']);
                } elseif (!empty($customerInfo['unique_id'])) {
                    $agreementNo = trim($customerInfo['unique_id']);
                }
            } catch (Throwable $e) {}
        }

        // Check Qty/Rate columns
        $includeQtyRate = false;
        if (is_array($invoiceSvc)) {
            foreach ($invoiceSvc as $svc) {
                if ((isset($svc['quantity']) && $svc['quantity'] !== null && $svc['quantity'] !== '') ||
                    (isset($svc['rate']) && $svc['rate'] !== null && $svc['rate'] !== '')) {
                    $includeQtyRate = true;
                    break;
                }
            }
        }

        $serviceRows = '';
        $count = 0;
        foreach ($invoiceSvc as $svc) {
            $count++;
            $desc = htmlspecialchars($svc['service_desc'] ?? '', ENT_QUOTES, 'UTF-8');
            $amtVal = (float)($svc['service_amount'] ?? 0);
            if ($includeQtyRate) {
                $qtyVal = isset($svc['quantity']) && $svc['quantity'] !== '' ? htmlspecialchars($svc['quantity']) : '-';
                $rateVal = isset($svc['rate']) && $svc['rate'] !== '' ? number_format((float)$svc['rate'], 2) : '-';
                $serviceRows .= '<tr style="border-top: none"><td>' . $count . '</td><td>' . $desc . '</td><td>' . $qtyVal . '</td><td>' . $rateVal . '</td><td>' . number_format($amtVal, 2) . '/-</td></tr>';
            } else {
                $serviceRows .= '<tr style="border-top: none"><td>' . $count . '</td><td>' . $desc . '</td><td>' . number_format($amtVal, 2) . '/-</td></tr>';
            }
        }

        $taxType = $invoice['tax_type'] ?? 'CGST & SGST';
        $gsts = '';
        if ($taxType === 'CGST & SGST') {
            $cgstVal = round($netPrice * 0.09, 2);
            $sgstVal = round($netPrice * 0.09, 2);
            $total_amt = round($netPrice + $cgstVal + $sgstVal, 2);
            $span = $includeQtyRate ? 'colspan="4"' : 'colspan="2"';
            $gsts = '<tr><td ' . $span . ' style="text-align:right">CGST@9%</td><td> ' . number_format($cgstVal, 2) . '/- </td></tr>'
                  . '<tr><td ' . $span . ' style="text-align:right">SGST@9%</td><td> ' . number_format($sgstVal, 2) . '/- </td></tr>';
        } elseif ($taxType === 'IGST') {
            $igstVal = round($netPrice * 0.18, 2);
            $total_amt = round($netPrice + $igstVal, 2);
            $span = $includeQtyRate ? 'colspan="4"' : 'colspan="2"';
            $gsts = '<tr><td ' . $span . ' style="text-align:right">IGST@18%</td><td> ' . number_format($igstVal, 2) . '/- </td></tr>';
        }

        $netColspan = $includeQtyRate ? 4 : 2;
        $finalColspan = $includeQtyRate ? 4 : 2;
        $wordsColspan = $includeQtyRate ? 5 : 3;
        $serviceHeader = $includeQtyRate ?
            '<tr><th width="5%">Sr.</th><th width="45%">Description</th><th width="10%">Qty</th><th width="15%">Rate</th><th width="25%">Amount in Rs. </th></tr>' :
            '<tr><th width="5%">Sr.</th><th width="60%">Description</th><th width="35%">Amount in Rs. </th></tr>';

        $refParts = [];
        if (!empty($agreementNo)) $refParts[] = '<b>Agreement No: </b>' . htmlspecialchars($agreementNo, ENT_QUOTES, 'UTF-8');
        if (!empty($poRef)) $refParts[] = '<b>P.O Ref: </b>' . htmlspecialchars($poRef, ENT_QUOTES, 'UTF-8');
        $refLineHtml = !empty($refParts) ? '<br><br>' . implode('<br>', $refParts) : '';

        $displayInvNo = $finalprefix !== '' ? $finalprefix . '-' . $invno : $invno;

        // Custom PDF class with specific header
        if (!class_exists('AITF_Invoice_Attachment_PDF')) {
            class AITF_Invoice_Attachment_PDF extends TCPDF {
                public function Header() {
                    $image_file = K_PATH_IMAGES . 'aitf.jpg';
                    if (file_exists($image_file)) {
                        $this->Image($image_file, 17, 5, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                    }
                    $this->SetFont('helvetica', 'B', 17.5);
                    $this->SetXY(60, 10);
                    $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    $this->SetFont('times', 'B', 12);
                    $this->Ln(7);
                    $this->SetXY(60, 15);
                    $this->SetTextColor(128, 184, 224);
                    $this->Cell(0, 15, '(NABCB ACCREDITED CERTIFICATION REGISTRAR FOR QMS)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    $this->SetFont('times', 'B', 12);
                    $this->Ln(7);
                    $this->SetXY(60, 20);
                    $this->SetTextColor(128, 128, 128);
                    $this->Cell(0, 15, '(TAX INVOICE)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                }
                public function Footer() {
                    $this->SetFont('times', 'I', 12);
                    $this->SetXY(0, -8);
                    $this->writeHTMLCell(0, 0, '', '', ' F 6.01', 0, 0, false, "L", true);
                }
            }
        }

        try {
            $pdf = new AITF_Invoice_Attachment_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('AITF');
            $pdf->SetAuthor('AITF');
            $pdf->SetTitle('Final Invoice - ' . $displayInvNo);
            $pdf->SetMargins(PDF_MARGIN_LEFT, 25, 5);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 9);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('times', '', 12);
            $pdf->AddPage();

            $html = '
            <table border="1" cellspacing="1" cellpadding="4">	
                <tr>
                    <td height="100"><b>Billed To - <br>NAME: ' . htmlspecialchars($customerInfo['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') . '</b><br><b>ADDRESS: </b>' . htmlspecialchars($customerInfo['address'] ?? '', ENT_QUOTES, 'UTF-8') . ' 
                        <br><br><b>GSTIN No.: </b>' . htmlspecialchars($customerInfo['gstin_no'] ?? '', ENT_QUOTES, 'UTF-8') . '
                        <br><b>STATE: </b>' . htmlspecialchars($state, ENT_QUOTES, 'UTF-8') . '&nbsp; &nbsp; &nbsp; <b>STATE CODE: </b>' . htmlspecialchars($stateCode, ENT_QUOTES, 'UTF-8') . '
                    </td>
                    <td><br><b> INVOICE NO:' . htmlspecialchars($displayInvNo, ENT_QUOTES, 'UTF-8') . '</b><br><br><b>DATE:</b> ' . date("d.m.Y", strtotime($invDate)) . $refLineHtml . '</td>
                </tr>
                <tr><td colspan="3"><b>SAC code : </b> 998311</td></tr>
            </table>
            <table border="1" cellspacing="1" cellpadding="4">
                ' . $serviceHeader . '
                ' . $serviceRows . '
                <tr><td colspan="' . $netColspan . '" style="text-align:right">Taxable Basic Amount</td><td> ' . number_format($netPrice, 2) . '/- </td></tr>
                ' . $gsts . '
                <tr><td colspan="' . $finalColspan . '" style="text-align:right">TOTAL INVOICE VALUE WITH TAX</td><td><b>' . number_format($total_amt, 2) . '/-</b></td></tr>
                <tr><td colspan="' . $wordsColspan . '"><b>
                NOTE-</b>
                <ul style="list-style-type:none;">
                    <li>* Please issue cheque in favor of AITF Management Certification Limited. Cash Payment is not acceptable</li>
                    <li>* <b>GSTIN No.: 27AAJCA6651K1ZB,</b></li>
                    <li>* PAN Number : ' . htmlspecialchars($firmInfo['pan_no'] ?? '', ENT_QUOTES, 'UTF-8') . '</li>
                    <li>* Bank details: IDBI Bank Ltd Address - ' . htmlspecialchars($firmInfo['bank_address'] ?? '', ENT_QUOTES, 'UTF-8') . '</li>
                    <li>* Current Account No.- 0087102000027849, IFSC Code - IBKL0000087</li>
                    <li>* For late payment interest will be charged at a rate of 10 % per month.</li>
                    <li>* Signature not required if invoice is computer generated and send by AITF registered A/c mail ID i.e. "aitfacct@gmail.com".</li>
                </ul>
                </td></tr>
            </table>
            <table border="1" cellspacing="1" cellpadding="4"> 
                <tr>
                    <td style="text-align:left;border-top:0.1px solid white"><b>Registered office Address -</b><br/>
                    ' . htmlspecialchars($firmInfo['firm_address'] ?? '', ENT_QUOTES, 'UTF-8') . '<br/>
                    (PH) ' . htmlspecialchars($firmInfo['firm_contact'] ?? '', ENT_QUOTES, 'UTF-8') . '<br/>
                    E mail : ' . htmlspecialchars($firmInfo['firm_email'] ?? '', ENT_QUOTES, 'UTF-8') . '<br/>
                    Web – www.aitf.co.in<br/>
                    </td>
                    <td style="text-align:right;border-bottom:0.1px solid white"><b>AITF MANAGEMENT CERTIFICATION LIMITED.</b><br/>
                    ' . ($signaturePath !== '' ? '<img src="' . $signaturePath . '" alt="logo" height="50" width="90"><br/>' : '') . '
                    Administrator
                    </td>
                </tr>
            </table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $pdf->Output($outputPath, 'F');
            return file_exists($outputPath) && filesize($outputPath) > 0;
        } catch (Throwable $e) {
            error_log("Error generating invoice PDF {$invoiceId}: " . $e->getMessage());
            return false;
        }
    }
}

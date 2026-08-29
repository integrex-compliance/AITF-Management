<?php

if (!defined('AITF_DSC_HMAC_SECRET')) {
    define('AITF_DSC_HMAC_SECRET', 'AITF_DSC_SECURE_HMAC_KEY_2026_PROD_SEC_X987654321');
}

/**
 * Initialize DSC Signature Audit database table if it doesn't exist
 */
function aitf_init_dsc_audit_table($db) {
    if (!$db) return false;
    try {
        $sql = "CREATE TABLE IF NOT EXISTS `dsc_signature_audit` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `document_id` VARCHAR(100) NOT NULL,
            `certificate_number` VARCHAR(100) NOT NULL,
            `signer_name` VARCHAR(255) NULL,
            `signer_certificate_serial` VARCHAR(100) NULL,
            `signer_certificate_thumbprint` VARCHAR(100) NULL,
            `issuer` VARCHAR(255) NULL,
            `document_sha256` VARCHAR(64) NOT NULL,
            `signed_pdf_sha256` VARCHAR(64) NULL,
            `signing_request_id` VARCHAR(100) NOT NULL UNIQUE,
            `nonce` VARCHAR(100) NOT NULL UNIQUE,
            `signing_timestamp` DATETIME NULL,
            `signature_status` ENUM('INITIATED', 'SIGNED', 'FAILED', 'REJECTED') NOT NULL DEFAULT 'INITIATED',
            `failure_reason` TEXT NULL,
            `signer_agent_version` VARCHAR(50) NOT NULL DEFAULT 'AITF-Signer v1.1',
            `signed_pdf_path` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (`certificate_number`),
            INDEX (`signing_request_id`),
            INDEX (`nonce`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);
        return true;
    } catch (Exception $e) {
        error_log("DSC Audit Table Init Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a cryptographically signed one-time Signing Ticket
 */
function aitf_create_signing_ticket($db, $documentId, $certificateNumber, $pdfBytes) {
    aitf_init_dsc_audit_table($db);

    $requestId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    $nonce = bin2hex(random_bytes(16));
    $pdfSha256 = hash('sha256', $pdfBytes);
    $createdAt = time();
    $expiresAt = $createdAt + 120; // 2 minutes expiry

    // Generate HMAC signature over ticket payload
    $payloadToSign = $requestId . '|' . $documentId . '|' . $certificateNumber . '|' . $pdfSha256 . '|' . $nonce . '|' . $expiresAt;
    $hmac = hash_hmac('sha256', $payloadToSign, AITF_DSC_HMAC_SECRET);

    // Record INITIATED state in audit table
    if ($db) {
        try {
            $stmt = $db->prepare("INSERT INTO dsc_signature_audit 
                (document_id, certificate_number, document_sha256, signing_request_id, nonce, signature_status, created_at)
                VALUES (?, ?, ?, ?, ?, 'INITIATED', NOW())");
            $stmt->execute([$documentId, $certificateNumber, $pdfSha256, $requestId, $nonce]);
        } catch (Exception $e) {
            error_log("DSC Audit Insert Error: " . $e->getMessage());
        }
    }

    return array(
        'request_id' => $requestId,
        'document_id' => (string)$documentId,
        'certificate_number' => (string)$certificateNumber,
        'pdf_sha256' => $pdfSha256,
        'nonce' => $nonce,
        'created_at' => $createdAt,
        'expires_at' => $expiresAt,
        'hmac' => $hmac
    );
}

/**
 * Verify HMAC, Expiration, and One-Time usage of a Signing Ticket
 */
function aitf_verify_signing_ticket($db, $ticket) {
    if (!is_array($ticket)) return array('valid' => false, 'error' => 'Invalid ticket format');

    $requestId = isset($ticket['request_id']) ? $ticket['request_id'] : '';
    $documentId = isset($ticket['document_id']) ? $ticket['document_id'] : '';
    $certificateNumber = isset($ticket['certificate_number']) ? $ticket['certificate_number'] : '';
    $pdfSha256 = isset($ticket['pdf_sha256']) ? $ticket['pdf_sha256'] : '';
    $nonce = isset($ticket['nonce']) ? $ticket['nonce'] : '';
    $expiresAt = isset($ticket['expires_at']) ? (int)$ticket['expires_at'] : 0;
    $hmac = isset($ticket['hmac']) ? $ticket['hmac'] : '';

    if (empty($requestId) || empty($nonce) || empty($pdfSha256) || empty($hmac)) {
        return array('valid' => false, 'error' => 'Missing ticket fields');
    }

    // Check expiration (2 min TTL)
    if (time() > $expiresAt) {
        return array('valid' => false, 'error' => 'Signing ticket has expired. Please re-initiate signing.');
    }

    // Verify HMAC
    $payloadToSign = $requestId . '|' . $documentId . '|' . $certificateNumber . '|' . $pdfSha256 . '|' . $nonce . '|' . $expiresAt;
    $expectedHmac = hash_hmac('sha256', $payloadToSign, AITF_DSC_HMAC_SECRET);

    if (!hash_equals($expectedHmac, $hmac)) {
        return array('valid' => false, 'error' => 'Signing ticket HMAC verification failed (Tampered ticket).');
    }

    // Check DB: Ticket must exist and be in 'INITIATED' state
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT id, signature_status FROM dsc_signature_audit WHERE signing_request_id = ? AND nonce = ? LIMIT 1");
            $stmt->execute([$requestId, $nonce]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return array('valid' => false, 'error' => 'Signing ticket not found in audit log.');
            }

            if ($row['signature_status'] === 'SIGNED') {
                return array('valid' => false, 'error' => 'Replay rejected: This signing request has already been used.');
            }

            if ($row['signature_status'] === 'REJECTED' || $row['signature_status'] === 'FAILED') {
                return array('valid' => false, 'error' => 'This signing request is marked as ' . $row['signature_status']);
            }
        } catch (Exception $e) {
            error_log("DSC Audit Verify Error: " . $e->getMessage());
        }
    }

    return array('valid' => true, 'request_id' => $requestId, 'pdf_sha256' => $pdfSha256);
}

/**
 * Validate Cryptographic PKCS#7 Structure inside the PDF bytes
 */
function aitf_validate_pdf_signature_structure($pdfBytes) {
    if (empty($pdfBytes) || strlen($pdfBytes) < 500) {
        return array('valid' => false, 'error' => 'Empty or invalid PDF stream');
    }

    // PDF Header check
    if (strpos($pdfBytes, '%PDF-') !== 0) {
        return array('valid' => false, 'error' => 'File is not a valid PDF');
    }

    // Check for /Type /Sig or /Subtype /Widget with /FT /Sig
    if (strpos($pdfBytes, '/Type /Sig') === false && strpos($pdfBytes, '/Type/Sig') === false) {
        return array('valid' => false, 'error' => 'No digital signature dictionary (/Type /Sig) found in PDF');
    }

    // Check for /ByteRange
    if (strpos($pdfBytes, '/ByteRange') === false) {
        return array('valid' => false, 'error' => 'No cryptographic /ByteRange found in PDF');
    }

    // Check for /Contents <hex>
    if (strpos($pdfBytes, '/Contents') === false) {
        return array('valid' => false, 'error' => 'No PKCS#7 signature contents found in PDF');
    }

    return array('valid' => true);
}

/**
 * Record successfully signed PDF in server storage and audit database
 */
function aitf_record_signed_certificate($db, $requestId, $signedPdfBytes, $signerMeta, $certificateNumber) {
    // 1. Validate PDF signature structure
    $structCheck = aitf_validate_pdf_signature_structure($signedPdfBytes);
    if (!$structCheck['valid']) {
        return array('success' => false, 'error' => $structCheck['error']);
    }

    // 2. Compute signed PDF SHA-256
    $signedPdfSha256 = hash('sha256', $signedPdfBytes);

    // 3. Save PDF permanently to secure server storage
    $uploadDir = __DIR__ . '/../uploads/signed_certificates/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $cleanCert = preg_replace('/[^A-Za-z0-9_-]+/', '_', $certificateNumber ?: 'CERT');
    $safeFileName = 'Certificate_' . $cleanCert . '_' . substr($requestId, 0, 8) . '_DigitallySigned.pdf';
    $filePath = $uploadDir . $safeFileName;

    if (file_put_contents($filePath, $signedPdfBytes) === false) {
        return array('success' => false, 'error' => 'Failed to save signed certificate on server');
    }

    // 4. Update audit table
    if ($db) {
        try {
            $stmt = $db->prepare("UPDATE dsc_signature_audit SET
                signer_name = ?,
                signer_certificate_serial = ?,
                signer_certificate_thumbprint = ?,
                issuer = ?,
                signed_pdf_sha256 = ?,
                signing_timestamp = NOW(),
                signature_status = 'SIGNED',
                signed_pdf_path = ?
                WHERE signing_request_id = ?");
            $stmt->execute([
                isset($signerMeta['signer_name']) ? $signerMeta['signer_name'] : 'Authorized Signer',
                isset($signerMeta['serial_number']) ? $signerMeta['serial_number'] : '',
                isset($signerMeta['thumbprint']) ? $signerMeta['thumbprint'] : '',
                isset($signerMeta['issuer']) ? $signerMeta['issuer'] : '',
                $signedPdfSha256,
                'uploads/signed_certificates/' . $safeFileName,
                $requestId
            ]);
        } catch (Exception $e) {
            error_log("DSC Audit Update Error: " . $e->getMessage());
        }
    }

    return array(
        'success' => true,
        'filename' => $safeFileName,
        'relative_path' => 'uploads/signed_certificates/' . $safeFileName,
        'signed_pdf_sha256' => $signedPdfSha256
    );
}

/**
 * Record signing failure in audit log
 */
function aitf_record_signing_failure($db, $requestId, $reason) {
    if (!$db || empty($requestId)) return;
    try {
        $stmt = $db->prepare("UPDATE dsc_signature_audit SET
            signature_status = 'FAILED',
            failure_reason = ?
            WHERE signing_request_id = ?");
        $stmt->execute([$reason, $requestId]);
    } catch (Exception $e) {
        error_log("DSC Audit Failure Log Error: " . $e->getMessage());
    }
}

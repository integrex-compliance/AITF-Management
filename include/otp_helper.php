<?php
/**
 * OTP Verification Helper for Application Form & Online Forms
 * Provides DB-backed persistence alongside session for 100% reliable verification on live servers.
 */

if (!function_exists('aitf_ensure_otp_table')) {
    function aitf_ensure_otp_table($db) {
        if (!$db) return;
        static $ensured = false;
        if ($ensured) return;
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS aitf_email_otps (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(191) NOT NULL,
                    otp VARCHAR(10) NOT NULL,
                    attempts INT DEFAULT 0,
                    created_at DATETIME NOT NULL,
                    verified_at DATETIME NULL,
                    INDEX idx_email_created (email, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $ensured = true;
        } catch (Exception $e) {
            error_log("OTP Table Ensure Error: " . $e->getMessage());
        }
    }
}

if (!function_exists('aitf_save_otp_record')) {
    function aitf_save_otp_record($db, $email, $otp) {
        if (!$db) return false;
        aitf_ensure_otp_table($db);
        try {
            $stmt = $db->prepare("INSERT INTO aitf_email_otps (email, otp, attempts, created_at) VALUES (:email, :otp, 0, NOW())");
            return $stmt->execute([
                ':email' => strtolower(trim($email)),
                ':otp' => trim($otp)
            ]);
        } catch (Exception $e) {
            error_log("Save OTP Error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('aitf_verify_otp_record')) {
    function aitf_verify_otp_record($db, $email, $enteredOtp) {
        if (!$db) return false;
        aitf_ensure_otp_table($db);
        $cleanEmail = strtolower(trim($email));
        $cleanOtp = preg_replace('/\D/', '', (string)$enteredOtp);
        if ($cleanOtp === '' || strlen($cleanOtp) < 4) return false;

        try {
            // Find recent active OTP records within last 15 minutes
            $stmt = $db->prepare("
                SELECT id, otp, attempts 
                FROM aitf_email_otps 
                WHERE email = :email 
                  AND created_at >= (NOW() - INTERVAL 15 MINUTE)
                  AND verified_at IS NULL
                ORDER BY id DESC
                LIMIT 5
            ");
            $stmt->execute([':email' => $cleanEmail]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($records as $rec) {
                if (hash_equals((string)$rec['otp'], (string)$cleanOtp)) {
                    // Mark as verified in DB
                    $upd = $db->prepare("UPDATE aitf_email_otps SET verified_at = NOW() WHERE id = :id");
                    $upd->execute([':id' => $rec['id']]);
                    return true;
                }
            }
        } catch (Exception $e) {
            error_log("Verify OTP DB Error: " . $e->getMessage());
        }
        return false;
    }
}

if (!function_exists('aitf_is_email_verified_db')) {
    function aitf_is_email_verified_db($db, $email) {
        if (!$db) return false;
        aitf_ensure_otp_table($db);
        try {
            $stmt = $db->prepare("
                SELECT id 
                FROM aitf_email_otps 
                WHERE email = :email 
                  AND verified_at >= (NOW() - INTERVAL 60 MINUTE)
                ORDER BY id DESC 
                LIMIT 1
            ");
            $stmt->execute([':email' => strtolower(trim($email))]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    }
}

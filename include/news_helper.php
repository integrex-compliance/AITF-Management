<?php
/**
 * News & Circulars Database Schema & Utility Helper
 * Ensures tables exist automatically in production or local environments without any dummy data.
 */

if (!function_exists('aitf_ensure_news_schema')) {
    function aitf_ensure_news_schema(PDO $db)
    {
        try {
            // 1. news_articles table
            $db->exec("CREATE TABLE IF NOT EXISTS news_articles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NULL,
                category VARCHAR(100) NOT NULL DEFAULT 'General Announcement',
                summary TEXT NULL,
                content LONGTEXT NOT NULL,
                image_path VARCHAR(255) NULL,
                document_path VARCHAR(255) NULL,
                document_name VARCHAR(255) NULL,
                author VARCHAR(100) DEFAULT 'Director / Management',
                status ENUM('published', 'draft') DEFAULT 'published',
                is_featured TINYINT(1) DEFAULT 0,
                views_count INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status),
                INDEX idx_category (category),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 2. news_subscribers table
            $db->exec("CREATE TABLE IF NOT EXISTS news_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(150) NOT NULL UNIQUE,
                status ENUM('pending', 'verified', 'unsubscribed') DEFAULT 'pending',
                verification_otp VARCHAR(10) NULL,
                otp_expiry DATETIME NULL,
                subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                verified_at DATETIME NULL,
                ip_address VARCHAR(50) NULL,
                INDEX idx_email (email),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // 3. Ensure upload directories exist
            $imgDir = __DIR__ . '/../uploads/news_images';
            $docDir = __DIR__ . '/../uploads/news_docs';
            if (!is_dir($imgDir)) {
                @mkdir($imgDir, 0777, true);
            }
            if (!is_dir($docDir)) {
                @mkdir($docDir, 0777, true);
            }
        } catch (Exception $e) {
            error_log('News schema error: ' . $e->getMessage());
        }
    }
}

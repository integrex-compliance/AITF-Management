<?php
/**
 * Backward compatibility wrapper for errorPage.php
 * Forwards to modern error.php while preserving legacy session messages
 */
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Forward any session error message
if (!isset($errorMessage) && isset($_SESSION['error_message']) && trim($_SESSION['error_message']) !== '') {
    $errorMessage = $_SESSION['error_message'];
}

if (!isset($errorCode)) {
    $errorCode = isset($_GET['code']) ? (int)$_GET['code'] : 404;
}

require __DIR__ . '/error.php';

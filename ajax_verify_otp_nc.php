<?php
session_start();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/client_email_validator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$check = aitf_check_client_email_exists($db, $email);

if ($check['found']) {
    $_SESSION['nc_closing_email_verified'] = $email;
    echo json_encode([
        'success' => true,
        'message' => 'Mail is verified, proceed to submit form.',
        'org_name' => $check['org_name'] ?? '',
        'contact_person' => $check['contact_person'] ?? '',
        'cert_no' => $check['cert_no'] ?? ''
    ]);
} else {
    unset($_SESSION['nc_closing_email_verified']);
    echo json_encode([
        'success' => false,
        'message' => 'This mail is not valid. Kindly mention the mail ID that is present in your application form.'
    ]);
}

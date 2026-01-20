<?php
session_start();
include_once './dbConnect.php';

// Simple file download handler
if (!isset($_SESSION['id'])) {
    die('Please login first');
}

$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
$fileType = isset($_GET['type']) ? $_GET['type'] : 'certification_aggrement';

if (!$customerId || !$serviceId) {
    die('Invalid parameters: Customer=' . $customerId . ', Service=' . $serviceId);
}

try {
    // Query based on file type
    if ($fileType == 'certification_aggrement') {
        $sql = "SELECT certification_aggrement as file_path FROM service_type_cr WHERE cr_id = ? AND customer_id = ?";
    } elseif ($fileType == 'application_form') {
        $sql = "SELECT application_form as file_path FROM service_type_cr WHERE cr_id = ? AND customer_id = ?";
    } elseif ($fileType == 'signed_notification') {
        $sql = "SELECT signed_notification as file_path FROM service_type_ca WHERE ca_id = ? AND customer_id = ?";
    } else {
        die('Unsupported file type: ' . $fileType);
    }
    
    $statement = $db->prepare($sql);
    $statement->execute([$serviceId, $customerId]);
    $result = $statement->fetch();
    
    if (!$result || empty($result['file_path'])) {
        die('No file found in database for Customer: ' . $customerId . ', Service: ' . $serviceId . ', Type: ' . $fileType);
    }
    
    $filePath = $result['file_path'];
    
    if (!file_exists($filePath)) {
        // File is missing from server - provide helpful message
        die('<div style="padding: 20px; border: 2px solid #ff6b6b; background: #ffe6e6; border-radius: 5px; font-family: Arial;">
                <h3 style="color: #c92a2a; margin-top: 0;">📄 File Not Found</h3>
                <p><strong>The requested file is not available on the server.</strong></p>
                <p><strong>Expected file:</strong> ' . htmlspecialchars($filePath) . '</p>
                <p><strong>Possible reasons:</strong></p>
                <ul>
                    <li>File was not uploaded yet</li>
                    <li>File was moved or deleted</li>
                    <li>Upload process was interrupted</li>
                </ul>
                <p><strong>Solution:</strong> Please re-upload the certification agreement file.</p>
                <p><a href="javascript:history.back()" style="background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">← Go Back</a></p>
             </div>');
    }
    
    // Force download
    $fileName = basename($filePath);
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

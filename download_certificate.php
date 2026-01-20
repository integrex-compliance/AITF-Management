<?php
session_start();
include_once './dbConnect.php';

// Get parameters
$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
$fileType = isset($_GET['type']) ? $_GET['type'] : 'certification_aggrement';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    die('Unauthorized access - Please login first');
}

if (!$customerId || !$serviceId) {
    http_response_code(400);
    die('Invalid parameters');
}

try {
    $filePath = null;
    $fileName = null;
    
    // Get file path based on type and service
    switch ($fileType) {
        case 'certification_aggrement':
            $sql = "SELECT certification_aggrement as file_path FROM service_type_cr WHERE cr_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Certification_Agreement';
            break;
        case 'application_form':
            $sql = "SELECT application_form as file_path FROM service_type_cr WHERE cr_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Application_Form';
            break;
        case 'signed_notification':
            $sql = "SELECT signed_notification as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Signed_Notification';
            break;
        case 'signed_copy':
            $sql = "SELECT signed_copy as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Signed_Copy';
            break;
        case 'audit_report_I':
            $sql = "SELECT audit_report_I as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Audit_Report_Stage_I';
            break;
        case 'signed_notification_II':
            $sql = "SELECT signed_notification_II as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Signed_Notification_Stage_II';
            break;
        case 'audit_report_II':
            $sql = "SELECT audit_report_II as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Audit_Report_Stage_II';
            break;
        case 'auditor_notes':
            $sql = "SELECT auditor_notes as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Auditor_Notes';
            break;
        case 'audit_nc_II':
            $sql = "SELECT audit_nc_II as file_path FROM service_type_ca WHERE ca_id = :service_id AND customer_id = :customer_id";
            $fileName = 'Audit_NC_Stage_II';
            break;
        default:
            http_response_code(400);
            die('Invalid file type');
    }
    
    $statement = $db->prepare($sql);
    $statement->bindParam(':service_id', $serviceId);
    $statement->bindParam(':customer_id', $customerId);
    $statement->execute();
    $result = $statement->fetch();
    
    if (!$result || empty($result['file_path'])) {
        http_response_code(404);
        die('File not found in database');
    }
    
    $filePath = $result['file_path'];
    
    // Check if file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File not found on server: ' . $filePath);
    }
    
    // Get file info
    $fileInfo = pathinfo($filePath);
    $originalFileName = $fileInfo['basename'];
    $extension = strtolower($fileInfo['extension']);
    $downloadFileName = $fileName . '.' . $extension;
    $fileSize = filesize($filePath);
    $mimeType = 'application/octet-stream';
    
    // Determine MIME type based on extension
    switch ($extension) {
        case 'pdf':
            $mimeType = 'application/pdf';
            break;
        case 'doc':
            $mimeType = 'application/msword';
            break;
        case 'docx':
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
        case 'xls':
            $mimeType = 'application/vnd.ms-excel';
            break;
        case 'xlsx':
            $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        case 'jpg':
        case 'jpeg':
            $mimeType = 'image/jpeg';
            break;
        case 'png':
            $mimeType = 'image/png';
            break;
        case 'gif':
            $mimeType = 'image/gif';
            break;
        case 'zip':
            $mimeType = 'application/zip';
            break;
    }
    
    // Set headers for file download
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Read and output file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    error_log('File download error: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error: ' . $e->getMessage());
}
?>

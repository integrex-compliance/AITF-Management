<?php

ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

include_once 'dbConnect.php';
require_once 'tcpdf_include.php';

function infoReviewFormatText($value)
{
    return nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function infoReviewFormatOption($value)
{
    $normalized = strtolower((string) $value);
    if ($normalized === 'yes' || $normalized === 'no') {
        return strtoupper($normalized[0]) . substr($normalized, 1);
    }
    return infoReviewFormatText($value);
}

function buildInfoReviewHtml(array $data)
{
    $html = '<h2 style="text-align:center;">AITF MANAGEMENT CERTIFICATION LIMITED</h2>';
    $html .= '<h3 style="text-align:center;">REVIEW OF INFORMATION UPDATION</h3>';
    $html .= '<p style="text-align:center;">The Review of Information Updation Form before the next audit for the changes submitted by the organisation since the last certification by AITF.</p>';
    $html .= '<table cellpadding="6" cellspacing="0" border="1" width="100%">';
    $html .= '<tr><td width="50%"><strong>Service ID</strong></td><td width="50%">' . infoReviewFormatText($data['service_id'] ?? '') . '</td></tr>';
    $html .= '<tr><td><strong>Customer ID</strong></td><td>' . infoReviewFormatText($data['customer_id'] ?? '') . '</td></tr>';
    $html .= '<tr><td><strong>Information Updation Form Received on Dt.</strong></td><td>' . infoReviewFormatText($data['info_form_received_date'] ?? '') . '</td></tr>';
    $html .= '</table>';

    $html .= '<br><table cellpadding="6" cellspacing="0" border="1" width="100%">';

    $pairs = [
        ['Company name – Any Change', 'company_change', 'If change, Elaborate', 'company_change_details'],
        ['Change of organisation ownership – Any Change', 'ownership_change', 'If change, Elaborate', 'ownership_change_details'],
        ['Change of Legal Status (Ltd / Pvt Ltd, etc.) – Any Change', 'legal_status_change', 'If change, Elaborate', 'legal_status_change_details'],
        ['Change/addition of Address/location(s) certified – Any Change', 'address_change', 'If change, Elaborate', 'address_change_details'],
        ['Employee strength – Any Change', 'employee_change', 'If change, Elaborate', 'employee_change_details'],
        ['Communication details – Any Change', 'communication_change', 'If change, Elaborate', 'communication_change_details'],
        ['Activities / Process – Any Change', 'activity_change', 'If change, Elaborate', 'activity_change_details'],
    ];

    foreach ($pairs as $pair) {
        $html .= '<tr>';
        $html .= '<td width="50%"><strong>' . $pair[0] . '</strong><br>' . infoReviewFormatOption($data[$pair[1]] ?? '') . '</td>';
        $html .= '<td width="50%"><strong>' . $pair[2] . '</strong><br>' . infoReviewFormatText($data[$pair[3]] ?? '') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    $html .= '<br><table cellpadding="6" cellspacing="0" border="1" width="100%">';
    $html .= '<tr><td width="100%"><strong>Review Comments</strong><br>' . infoReviewFormatText($data['review_comments'] ?? '') . '</td></tr>';
    $html .= '<tr><td><strong>Reviewed by</strong><br>' . infoReviewFormatText($data['reviewer_name'] ?? '') . '</td></tr>';
    $html .= '<tr><td><strong>Review Date</strong><br>' . infoReviewFormatText($data['review_date'] ?? '') . '</td></tr>';
    $html .= '</table>';

    return $html;
}

function createInfoReviewPdf(array $data, $pdfPath)
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle('Review of Information Updation');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 11);
    $pdf->writeHTML(buildInfoReviewHtml($data), true, false, true, false, '');
    $pdf->Output($pdfPath, 'F');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $serviceId = $_POST['info_review_service_id'] ?? '';
        $customerId = $_POST['info_review_customer_id'] ?? '';
        
        // Collect form data with null coalescing to prevent undefined index errors
        $infoFormReceivedDate = $_POST['info_form_received_date'] ?? '';
        $companyChange = $_POST['company_change'] ?? '';
        $companyChangeDetails = $_POST['company_change_details'] ?? '';
        $ownershipChange = $_POST['ownership_change'] ?? '';
        $ownershipChangeDetails = $_POST['ownership_change_details'] ?? '';
        $legalStatusChange = $_POST['legal_status_change'] ?? '';
        $legalStatusChangeDetails = $_POST['legal_status_change_details'] ?? '';
        $addressChange = $_POST['address_change'] ?? '';
        $addressChangeDetails = $_POST['address_change_details'] ?? '';
        $employeeChange = $_POST['employee_change'] ?? '';
        $employeeChangeDetails = $_POST['employee_change_details'] ?? '';
        $communicationChange = $_POST['communication_change'] ?? '';
        $communicationChangeDetails = $_POST['communication_change_details'] ?? '';
        $activityChange = $_POST['activity_change'] ?? '';
        $activityChangeDetails = $_POST['activity_change_details'] ?? '';
        $reviewComments = $_POST['review_comments'] ?? '';
        $reviewerName = $_POST['reviewer_name'] ?? '';
        $reviewDate = $_POST['review_date'] ?? '';
        
        // For now, just save to a file instead of database to test
        $data = array(
            'service_id' => $serviceId,
            'customer_id' => $customerId,
            'info_form_received_date' => $infoFormReceivedDate,
            'company_change' => $companyChange,
            'company_change_details' => $companyChangeDetails,
            'ownership_change' => $ownershipChange,
            'ownership_change_details' => $ownershipChangeDetails,
            'legal_status_change' => $legalStatusChange,
            'legal_status_change_details' => $legalStatusChangeDetails,
            'address_change' => $addressChange,
            'address_change_details' => $addressChangeDetails,
            'employee_change' => $employeeChange,
            'employee_change_details' => $employeeChangeDetails,
            'communication_change' => $communicationChange,
            'communication_change_details' => $communicationChangeDetails,
            'activity_change' => $activityChange,
            'activity_change_details' => $activityChangeDetails,
            'review_comments' => $reviewComments,
            'reviewer_name' => $reviewerName,
            'review_date' => $reviewDate
        );
        
        // Save to JSON file for now (temporary solution)
        $uploadDir = __DIR__ . '/uploads/reviews/';
        $filename = $uploadDir . 'data_' . $serviceId . '_' . $customerId . '.json';
        $pdfFilename = $uploadDir . 'info_review_' . $serviceId . '_' . $customerId . '.pdf';
        $relativePdfPath = 'uploads/reviews/info_review_' . $serviceId . '_' . $customerId . '.pdf';
        
        // Ensure directory exists with proper permissions
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
            chmod($uploadDir, 0777);
        }
        
        // Save JSON data
        if (!file_put_contents($filename, json_encode($data))) {
            throw new Exception('Failed to save JSON data');
        }
        
        // Create PDF
        createInfoReviewPdf($data, $pdfFilename);
        
        // Try to update the service record to mark that review is saved
        // This helps the UI know when to show pencil+download vs plus icon
        try {
            $serviceTableMap = array(
                array('table' => 'ca_services', 'idColumn' => 'ca_id'),
                array('table' => 'sai_services', 'idColumn' => 'sai_id'),
                array('table' => 'saii_services', 'idColumn' => 'saii_id'),
                array('table' => 'rc_services', 'idColumn' => 'rc_id'),
                array('table' => 'sp_services', 'idColumn' => 'sp_id')
            );

            foreach ($serviceTableMap as $serviceTable) {
                $checkQuery = "SELECT 1 FROM {$serviceTable['table']} WHERE {$serviceTable['idColumn']} = :service_id LIMIT 1";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':service_id', $serviceId);
                $checkStmt->execute();

                if ($checkStmt->fetchColumn()) {
                    $updateQuery = "UPDATE {$serviceTable['table']} SET info_update_review = :filename WHERE {$serviceTable['idColumn']} = :service_id";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(':filename', $relativePdfPath);
                    $updateStmt->bindParam(':service_id', $serviceId);
                    $updateStmt->execute();
                    break;
                }
            }
        } catch (Exception $dbError) {
            // Ignore database errors for now - the file is still saved
            error_log('Database update failed: ' . $dbError->getMessage());
        }

        // Clear any buffered output
        ob_end_clean();
        
        // Set JSON header and return success response
        header('Content-Type: application/json');
        $response = array('success' => true, 'downloadUrl' => $relativePdfPath, 'message' => 'Data saved successfully');
        echo json_encode($response);
        exit;

    } catch (Exception $e) {
        // Clear any buffered output
        ob_end_clean();
        
        // Set JSON header and return error response
        header('Content-Type: application/json');
        $response = array('success' => false, 'error' => $e->getMessage());
        echo json_encode($response);
        exit;
    }
} else {
    // Clear any buffered output
    ob_end_clean();
    
    // Set JSON header and return error response
    header('Content-Type: application/json');
    $response = array('success' => false, 'error' => 'Invalid request method');
    echo json_encode($response);
    exit;
}
?>

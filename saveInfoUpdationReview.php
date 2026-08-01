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
    $html = '<p style="text-align:justify; font-size:10px; color:#333333; line-height:1.4;">The Review of Information Updation Form before the next audit for the changes submitted by the organisation since the last certification by AITF.</p><br>';
    
    // Top Info Table
    $html .= '<table cellpadding="10" cellspacing="0" border="1" width="100%">';
    $html .= '<tr>';
    $html .= '<td width="60%"><strong>Client Name:</strong> ' . infoReviewFormatText($data['customer_name'] ?? '') . '</td>';
    $html .= '<td width="40%"><strong>Agreement No:</strong> ' . infoReviewFormatText($data['agreement_no'] ?? '') . '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    // Aspects / Changes Table
    $html .= '<br><br><table cellpadding="10" cellspacing="0" border="1" width="100%">';
    $html .= '<thead>';
    $html .= '<tr style="background-color:#f2f2f2; color:#000000; font-weight:bold; text-align:center;">';
    $html .= '  <th width="50%" align="center"><strong>Aspects / Changes</strong></th>';
    $html .= '  <th width="50%" align="center"><strong>Elaborate (If Change)</strong></th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $pairs = [
        ['Company name – Any Change', 'company_change', 'company_change_details'],
        ['Change of organisation ownership – Any Change', 'ownership_change', 'ownership_change_details'],
        ['Change of Legal Status (Ltd / Pvt Ltd, etc.) – Any Change', 'legal_status_change', 'legal_status_change_details'],
        ['Change/addition of Address/location(s) certified – Any Change', 'address_change', 'address_change_details'],
        ['Employee strength – Any Change', 'employee_change', 'employee_change_details'],
        ['Communication details – Any Change', 'communication_change', 'communication_change_details'],
        ['Activities / Process – Any Change', 'activity_change', 'activity_change_details'],
    ];

    foreach ($pairs as $pair) {
        $isNoChange = (strtolower(trim((string)($data[$pair[1]] ?? ''))) === 'no');
        $elaborateText = infoReviewFormatText($data[$pair[2]] ?? '');
        if ($isNoChange && empty(trim((string)($data[$pair[2]] ?? '')))) {
            $elaborateText = '<strong style="font-size:12px;">—</strong>';
        }

        $html .= '<tr>';
        $html .= '  <td width="50%" style="font-size:10.5px;"><strong>' . $pair[0] . ':</strong> ' . infoReviewFormatOption($data[$pair[1]] ?? '') . '</td>';
        $html .= '  <td width="50%" style="font-size:10.5px;">' . $elaborateText . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';

    // Review Comments Table
    $html .= '<br><br><table cellpadding="10" cellspacing="0" border="1" width="100%">';
    $html .= '<tr><td width="100%" style="background-color:#f2f2f2; font-weight:bold;"><strong>Review Comments / Conclusion:</strong></td></tr>';
    $html .= '<tr><td width="100%" style="font-size:10.5px;">' . (empty($data['review_comments']) ? 'N/A' : infoReviewFormatText($data['review_comments'])) . '</td></tr>';
    $html .= '</table>';

    // Reviewer Footer Table
    $html .= '<br><table cellpadding="10" cellspacing="0" border="1" width="100%">';
    $html .= '<tr>';
    $html .= '  <td width="50%"><strong>Reviewed by:</strong> ' . infoReviewFormatText($data['reviewer_name'] ?? '') . '</td>';
    $html .= '  <td width="50%"><strong>Review Date:</strong> ' . infoReviewFormatText($data['review_date'] ?? '') . '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    
    $html .= '<br><p style="text-align:center; font-size:8.5px; color:#555555;"><strong>Form No. F 7.31 Annex 1 | Rev:00 | Date: 01.06.2025 | Page: 1 of 1</strong></p>';

    return $html;
}

// Extend TCPDF for header/footer logo
class MYPDF_InfoReview extends TCPDF
{
    public function Header()
    {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (@file_exists($image_file)) {
            $this->Image($image_file, 17, 4, 40, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $this->SetTextColor(11, 42, 122);
        $this->SetFont('helvetica', 'B', 17.5);
        $this->SetXY(60, 9);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(50, 16);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 11.5);
        $this->Cell(0, 25, 'REVIEW OF INFORMATION UPDATION', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetLineStyle(['width' => 0.40, 'color' => [0, 0, 0]]);
        $this->Line($this->original_lMargin, 26, $this->w - $this->original_rMargin, 26);
    }
}

function createInfoReviewPdf(array $data, $pdfPath)
{
    $pdf = new MYPDF_InfoReview('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('AITF Management Certification Limited');
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle('Review of Information Updation');
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 33, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10.5);
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
        
        // Fetch customer name
        $customerName = '';
        try {
            $customerQuery = "SELECT customer_name FROM customer_info WHERE id = :customer_id LIMIT 1";
            $customerStmt = $db->prepare($customerQuery);
            $customerStmt->bindParam(':customer_id', $customerId);
            $customerStmt->execute();
            $customerName = $customerStmt->fetchColumn() ?: '';
        } catch (Exception $e) {
            error_log('Error fetching customer name: ' . $e->getMessage());
        }

        // Fetch agreement number
        $agreementNo = '';
        try {
            $agreementSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND service = :service_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
            $agreementStmt = $db->prepare($agreementSql);
            $agreementStmt->bindParam(':customer_id', $customerId);
            $agreementStmt->bindParam(':service_id', $serviceId);
            $agreementStmt->execute();
            $agreementNo = $agreementStmt->fetchColumn() ?: '';

            if (empty($agreementNo)) {
                $fallbackSql = "SELECT aggrement_no FROM certification_aggrement WHERE customer_id = :customer_id AND aggrement_no IS NOT NULL AND aggrement_no != '' ORDER BY id DESC LIMIT 1";
                $fallbackStmt = $db->prepare($fallbackSql);
                $fallbackStmt->bindParam(':customer_id', $customerId);
                $fallbackStmt->execute();
                $agreementNo = $fallbackStmt->fetchColumn() ?: '';
            }
        } catch (Exception $e) {
            error_log('Error fetching agreement no: ' . $e->getMessage());
        }

        // Prepare data array
        $data = array(
            'service_id' => $serviceId,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'agreement_no' => $agreementNo,
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

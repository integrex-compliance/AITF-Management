<?php
include_once 'dbConnect.php';
require_once 'tcpdf_include.php';

// Get parameters
$serviceId = isset($_GET['service']) ? $_GET['service'] : '';
$customerId = isset($_GET['customer']) ? $_GET['customer'] : '';

if (empty($serviceId) || empty($customerId)) {
    die('Invalid parameters');
}

// File paths
$uploadDir = __DIR__ . '/uploads/reviews/';
$pdfFilename = $uploadDir . 'info_review_' . $serviceId . '_' . $customerId . '.pdf';
$jsonFilename = $uploadDir . 'data_' . $serviceId . '_' . $customerId . '.json';

// Fetch customer name dynamically if needed
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

// Fetch agreement number dynamically if needed
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

// Determine if we need to regenerate the PDF from JSON
$regenerate = false;
$data = [];

if (file_exists($jsonFilename)) {
    $data = json_decode(file_get_contents($jsonFilename), true);
    $data['customer_name'] = $customerName;
    $data['agreement_no'] = $agreementNo;
    $regenerate = true;
}

// If JSON data exists, regenerate PDF to guarantee the latest template and database changes
if ($regenerate && !empty($data)) {
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
    
    // Save to server
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $pdf->Output($pdfFilename, 'F');
}

if (file_exists($pdfFilename)) {
    // Serve inline directly to Chrome PDF viewer
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Information_Updation_Review_' . $serviceId . '.pdf"');
    header('Content-Length: ' . filesize($pdfFilename));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($pdfFilename);
    exit;
} else {
    die('No PDF found for this service');
}
?>

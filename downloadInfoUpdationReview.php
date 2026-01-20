<?php
include_once 'dbConnect.php';
require_once 'tcpdf_include.php';

// Get parameters
$serviceId = isset($_GET['service']) ? $_GET['service'] : '';
$customerId = isset($_GET['customer']) ? $_GET['customer'] : '';

if (empty($serviceId) || empty($customerId)) {
    die('Invalid parameters');
}

// Check if PDF already exists
$uploadDir = __DIR__ . '/uploads/reviews/';
$pdfFilename = $uploadDir . 'info_review_' . $serviceId . '_' . $customerId . '.pdf';
$jsonFilename = $uploadDir . 'data_' . $serviceId . '_' . $customerId . '.json';

if (file_exists($pdfFilename)) {
    // PDF exists, serve it for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Information_Updation_Review_' . $serviceId . '.pdf"');
    header('Content-Length: ' . filesize($pdfFilename));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($pdfFilename);
    exit;
} else {
    // PDF doesn't exist, check if JSON data exists to regenerate
    if (file_exists($jsonFilename)) {
        // Load data from JSON and regenerate PDF
        $data = json_decode(file_get_contents($jsonFilename), true);
        
        // Include the PDF generation functions from saveInfoUpdationReview.php
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

        // Generate PDF
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
        
        // Save PDF
        $pdf->Output($pdfFilename, 'F');
        
        // Now serve it for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Information_Updation_Review_' . $serviceId . '.pdf"');
        header('Content-Length: ' . filesize($pdfFilename));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($pdfFilename);
        exit;
    } else {
        die('No data found for this service');
    }
}
?>

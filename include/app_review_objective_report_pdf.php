<?php
require_once __DIR__ . '/../tcpdf_include.php';
require_once __DIR__ . '/app_review_objective_report_lib.php';

if (!class_exists('APPREVIEWOBJECTIVEPDF')) {
class APPREVIEWOBJECTIVEPDF extends TCPDF {
    public $reportFromDate = '';
    public $reportToDate = '';
    public $reportClientName = '';

    public function Header() {
        $imageFile = K_PATH_IMAGES . 'aitf.jpg';
        if (file_exists($imageFile)) {
            $this->Image($imageFile, 10, 6, 40, '', 'JPEG', '', 'T', false, 300);
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 21);
        $this->SetY(6);
        $this->SetX(50);
        $this->Cell(237, 12, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C');

        $this->SetFont('helvetica', 'B', 14);
        $this->SetX(50);
        $this->Cell(237, 9, 'APPLICATION REVIEW OBJECTIVE REPORT', 0, 1, 'C');

        $filters = appReviewObjectiveFilterText(
            $this->reportFromDate,
            $this->reportToDate,
            $this->reportClientName
        );
        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(50);
        $this->Cell(237, 6, strtoupper($filters), 0, 1, 'C');
        $this->Line(10, 38, 287, 38);
    }

    public function Footer() {
        $this->SetY(-22);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'NOTE: THIS IS A SYSTEM GENERATED DOCUMENT AND DOES NOT REQUIRE A PHYSICAL SIGNATURE.', 0, 1, 'C');
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 8, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}
}

function buildAppReviewObjectiveReportPdfBytes($records, $fromDate, $toDate, $clientName = '') {
    $pdf = new APPREVIEWOBJECTIVEPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->reportFromDate = $fromDate;
    $pdf->reportToDate = $toDate;
    $pdf->reportClientName = $clientName;
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AITF');
    $pdf->SetTitle('Application Review Objective Report');
    $pdf->SetMargins(10, 42, 10);
    $pdf->SetAutoPageBreak(true, 25);
    if (method_exists($pdf, 'setFontSubsetting')) {
        $pdf->setFontSubsetting(false);
    }
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 9);

    $html = '<table border="1" cellpadding="7">
        <thead>
            <tr style="background-color:#163c6e;color:#ffffff;font-weight:bold;">
                <th width="5%" align="center">Sr.</th>
                <th width="25%">Client Name</th>
                <th width="17%" align="center">Application Received Date</th>
                <th width="17%" align="center">Review of Application Date</th>
                <th width="8%" align="center">Days</th>
                <th width="28%">Remark(s), if any</th>
            </tr>
        </thead>
        <tbody>';

    $withinTarget = 0;
    $delayed = 0;
    $pendingReview = 0;

    if (empty($records)) {
        $html .= '<tr><td colspan="6" align="center">No records found</td></tr>';
    } else {
        $serial = 1;
        foreach ($records as $record) {
            $days = appReviewObjectiveDays($record['application_rcv_date'], $record['review_date']);
            if ($days === null) {
                $pendingReview++;
                $rowStyle = '';
                $daysText = '-';
            } elseif ($days > 2) {
                $delayed++;
                $rowStyle = 'background-color:#ffc7ce;color:#9c0006;';
                $daysText = (string) $days;
            } else {
                $withinTarget++;
                $rowStyle = 'background-color:#c6efce;color:#006100;';
                $daysText = (string) $days;
            }

            $html .= '<tr style="' . $rowStyle . '">'
                . '<td width="5%" align="center">' . $serial++ . '</td>'
                . '<td width="25%"><b>' . htmlspecialchars($record['customer_name'], ENT_QUOTES, 'UTF-8') . '</b></td>'
                . '<td width="17%" align="center">' . appReviewObjectiveDisplayDate($record['application_rcv_date']) . '</td>'
                . '<td width="17%" align="center">' . appReviewObjectiveDisplayDate($record['review_date']) . '</td>'
                . '<td width="8%" align="center"><b>' . $daysText . '</b></td>'
                . '<td width="28%">' . nl2br(htmlspecialchars((string) $record['app_obj_comment'], ENT_QUOTES, 'UTF-8')) . '</td>'
                . '</tr>';
        }
    }
    $html .= '</tbody></table>';

    $html .= '<br><br><table border="1" cellpadding="7">'
        . '<tr><th colspan="2" align="center" style="background-color:#163c6e;color:#ffffff;font-weight:bold;">REPORT SUMMARY</th></tr>'
        . '<tr><td width="70%"><b>Total Records</b></td><td width="30%" align="center"><b>' . count($records) . '</b></td></tr>'
        . '<tr style="background-color:#c6efce;color:#006100;"><td width="70%"><b>Reviewed Within 2 Days</b></td><td width="30%" align="center"><b>' . $withinTarget . '</b></td></tr>'
        . '<tr style="background-color:#ffc7ce;color:#9c0006;"><td width="70%"><b>Reviewed After 2 Days</b></td><td width="30%" align="center"><b>' . $delayed . '</b></td></tr>'
        . '<tr><td width="70%"><b>Review Date Pending</b></td><td width="30%" align="center"><b>' . $pendingReview . '</b></td></tr>'
        . '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    return $pdf->Output('Application_Review_Objective_Report.pdf', 'S');
}

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'tcpdf_include.php';
require_once 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$submission = null;

if ($id > 0 && isset($db)) {
    try {
        $stmt = $db->prepare("SELECT * FROM nc_closing_forms WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        die("Error fetching submission");
    }
}

if (!$submission) {
    die("Submission not found");
}

function ncUpdH($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$logoPath = __DIR__ . '/images/aitf.jpg';
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/images/logo.png';
}

class NCPDF extends TCPDF {
    public function Header() {}
    public function Footer() {} // No automatic footer, we'll draw the custom footer block manually
}

$pdf = new NCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('NC Closing Form - ' . $submission['org_name']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 2, 12);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 9.5);
$pdf->AddPage();

$logoImgHtml = file_exists($logoPath) ? '<img src="'.$logoPath.'" style="height:45px; width:auto;">' : '<b>AITF</b>';

$sigImgHtml = '<br><br>_______________';
$sigFileCandidate = $submission['signature_image'] ?? $submission['signature_image_path'] ?? '';
if (!empty($sigFileCandidate)) {
    $abs_sig_path = __DIR__ . '/' . $sigFileCandidate;
    $abs_sig_path_fwd = str_replace('\\', '/', $abs_sig_path);
    if (file_exists($abs_sig_path) || file_exists($abs_sig_path_fwd)) {
        $sigImgHtml = '<img src="' . $abs_sig_path . '" style="height: 60px; width: auto;">';
    }
}

$attList = [];
if (!empty($submission['attachment1'])) {
    $attList[] = 'Attachment 1 (' . strtoupper(pathinfo($submission['attachment1'], PATHINFO_EXTENSION)) . ')';
}
if (!empty($submission['attachment2'])) {
    $attList[] = 'Attachment 2 (' . strtoupper(pathinfo($submission['attachment2'], PATHINFO_EXTENSION)) . ')';
}
$attHtml = '';
if (!empty($attList)) {
    $attHtml = '<tr><td style="font-size: 8pt; background-color: #f8fafc; padding: 4px;"><b>Supporting Attachments Uploaded:</b> ' . implode(', ', $attList) . '</td></tr>';
}

// Helper for checkboxes
function chk($val, $match, $isContains = false) {
    if ($isContains) {
        return (strpos($val, $match) !== false) ? '&#9746;' : '&#9744;'; // Checked / Unchecked box characters
    }
    return ($val == $match) ? '&#9746;' : '&#9744;';
}
function txtBox($checked) {
    return $checked ? '&#9746;' : '&#9744;';
}

$html = '
<table border="0" cellpadding="1" cellspacing="0" width="100%">
    <tr>
        <td width="30%" align="center" style="vertical-align:middle;">
            ' . $logoImgHtml . '
        </td>
        <td width="70%" align="center" style="vertical-align:middle;">
            <div style="font-size: 14pt; font-weight: bold;">AITF MANAGEMENT CERTIFICATION LIMITED</div>
            <div style="font-size: 12pt; font-weight: bold; text-decoration: underline;">NONCONFORMANCE CLOSER REPORT</div>
        </td>
    </tr>
</table>
<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <tr>
        <td width="50%" rowspan="4">
            ORGANIZATION:<br><br>
            <b>' . nl2br(ncUpdH($submission['org_name'])) . '</b><br><br>
            Mail: ' . ncUpdH($submission['email']) . '
        </td>
        <td width="50%">STANDARD: <b>' . ncUpdH($submission['standard']) . '</b></td>
    </tr>
    <tr>
        <td width="50%">Date of audit: <b>' . ncUpdH($submission['audit_date']) . '</b></td>
    </tr>
    <tr>
        <td width="50%">Nonconformance reference no: <b>' . ncUpdH($submission['nc_reference_no']) . '</b></td>
    </tr>
    <tr>
        <td width="50%">Date of corrective action: <b>' . ncUpdH($submission['corrective_action_date']) . '</b></td>
    </tr>
</table>

<div style="font-size: 8pt; margin-top: 3px; margin-bottom: 3px; color: #0056b3;">
    *Note: Auditee organization shall provide a plan for correction and corrective action with root cause analysis for the Minor and MAJOR Nonconformance identified during audit. However, Submission of evidence of the implementation is compulsory for the MAJOR NC.
</div>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <tr style="background-color: #e2e2e2;">
        <td align="center">
            <b>*ROOT CAUSE ANALYSIS, PLAN FOR CORRECTION AND CORRECTIVE ACTION (Submission by Auditee Organization)</b><br>
            <span style="font-size: 7.5pt; font-weight: normal;">Please refer to Form F 7.24 – for non-conformance issued.</span>
        </td>
    </tr>
    <tr>
        <td>
            <b>Root-cause:</b><br>
            ' . nl2br(ncUpdH($submission['root_cause'])) . '<br><br>
        </td>
    </tr>
    <tr>
        <td>
            <b>Correction:</b><br>
            ' . nl2br(ncUpdH($submission['correction'])) . '<br><br>
        </td>
    </tr>
    <tr>
        <td>
            <b>Corrective Action (action to prevent recurrence):</b><br>
            ' . nl2br(ncUpdH($submission['corrective_action'])) . '<br><br>
        </td>
    </tr>
    <tr style="background-color: #e2e2e2;">
        <td align="center">
            <b>ORGANIZATION PERSON RESPONSIBLE FOR IMPLEMENTING CORRECTIVE ACTION</b>
        </td>
    </tr>
    <tr>
        <td style="padding: 10px;">
            <table cellpadding="4" cellspacing="0" border="0" width="100%">
                <tr>
                    <td width="60%" style="vertical-align: top; line-height: 2;">
                        <b>Name:</b> ' . ncUpdH($submission['sign_name']) . '<br>
                        <b>Designation:</b> ' . ncUpdH($submission['sign_designation']) . '<br>
                        <b>Proposed Completion Dt.:</b> ' . ncUpdH($submission['proposed_completion_date']) . '
                    </td>
                    <td width="40%" style="vertical-align: top; text-align: center;">
                        <b>Signature</b><br><br>
                        ' . $sigImgHtml . '
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    ' . $attHtml . '
    <tr>
        <td align="center">
            FOR VERIFICATION BY AITF ASSESSOR
        </td>
    </tr>
    <tr>
        <td style="padding: 0;">
            <table border="1" cellpadding="4" cellspacing="0" width="100%" style="border-collapse:collapse;">
                <tr>
                    <td width="50%">
                        <b>Accepted :</b> Yes ' . chk($submission['assessor_accepted'], 'Yes') . '
                    </td>
                    <td width="50%">
                        <b>Not Accepted :</b> ' . chk($submission['assessor_accepted'], 'No') . ' 
                        &nbsp;&nbsp; Further action required by client ' . chk($submission['assessor_accepted'], 'Further action') . '
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        Based on<br>
                        Correction & corrective action received ' . chk($submission['assessor_verification_method'], 'Correction & corrective action received', true) . ' (for minor NC*)<br>
                        Verification of supporting evidence/s ' . chk($submission['assessor_verification_method'], 'Verification of supporting evidence/s', true) . '<br>
                        Onsite verification ' . chk($submission['assessor_verification_method'], 'Onsite verification', true) . '<br><br>
                        <b>Verification by Team Leader /Assessor</b><br><br>
                        (Name): ' . ncUpdH($submission['team_leader_name']) . '<br><br>
                        (Signature): ' . ncUpdH($submission['team_leader_signature']) . '<br><br>
                        Date: ' . ncUpdH($submission['team_leader_date']) . '
                    </td>
                    <td width="50%">
                        Comments:<br>
                        ' . nl2br(ncUpdH($submission['assessor_comments'])) . '<br><br><br>
                        <b>Verification by Team Leader /Assessor</b><br><br>
                        (Name): ' . ncUpdH($submission['assessor_name']) . '<br><br>
                        (Signature): ' . ncUpdH($submission['assessor_signature']) . '<br><br>
                        Date: ' . ncUpdH($submission['assessor_date']) . '
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b>Follow-up comments*</b> (during the next surveillance / recertification) audit: Effectiveness of corrective action/s taken by client verified and found satisfactory Yes ' . txtBox($submission['follow_up_effectiveness'] == 'Satisfactory - Yes') . ' / No ' . txtBox($submission['follow_up_effectiveness'] == 'Satisfactory - No') . '<br><br>
                        ' . txtBox(false) . ' <b>Not effective ?</b> (signature)<br>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="60%"></td>
                                <td width="40%" align="right">
                                    Sign of verifying assessor: ' . ncUpdH($submission['verifying_assessor_sign']) . '<br>
                                    Date: ' . ncUpdH($submission['verifying_assessor_date']) . '
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table border="1" cellpadding="2" cellspacing="0" width="100%" style="font-size: 8pt; margin-top: 5px;">
    <tr>
        <td colspan="3">Confidential when complete</td>
    </tr>
    <tr>
        <td width="33%">Form No. F 7.24 (Annexure-A)</td>
        <td width="33%">Rev.:03, Date: 13.05.2017</td>
        <td width="34%">Issue.: 01, Date: 13.05.2017</td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

$pdfOutputMode = isset($pdfOutputMode) ? $pdfOutputMode : 'I';
$pdfOutputPath = isset($pdfOutputPath) ? $pdfOutputPath : 'NC_Closing_Report_' . $id . '.pdf';

if ($pdfOutputMode === 'F') {
    $pdf->Output($pdfOutputPath, 'F');
} else {
    $pdf->Output($pdfOutputPath, 'I');
}

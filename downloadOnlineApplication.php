<?php

require_once 'tcpdf_include.php';
include 'dbConnect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0 || !isset($db)) {
    die('Invalid application.');
}

$outputDest = (defined('ATTACH_PDF') && ATTACH_PDF) ? 'S' : 'D';

try {
    $stmt = $db->prepare("SELECT * FROM online_audit_applications WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $application = false;
}

if (!$application) {
    die('Application not found.');
}

class ApplicationFormPDF extends TCPDF
{
    protected $tcpdflink = false;

    public function Header()
    {
        // Logo
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (@file_exists($image_file)) {
            $this->Image($image_file, 10, 4, 38, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Title
        $this->SetXY(46, 5);
        $this->SetFont('helvetica', 'B', 13.5);
        $this->Cell(154, 6, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, 1, 'C', 0, '', 0, false, 'M', 'M');

        // Subtitle & Page number
        $this->SetXY(46, 11.5);
        $this->SetFont('helvetica', 'B', 11.5);
        $this->Cell(130, 5.5, 'Audit Application Form', 0, 0, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', '', 8.5);
        $currentPage = $this->getPage();
        $pageText = ($currentPage <= 4) ? ('Page ' . $currentPage . ' of 4') : ('Page ' . $currentPage);
        $this->Cell(24, 5.5, $pageText, 0, 1, 'R', 0, '', 0, false, 'M', 'M');

        // Horizontal line
        $this->SetLineStyle(array('width' => 0.65, 'color' => array(0, 0, 0)));
        $this->Line(10, 19.5, 200, 19.5);
    }

    public function Footer()
    {
        $this->SetY(-10);
        if ($this->getPage() <= 4) {
            $this->SetFont('helvetica', '', 8.5);
            $this->Cell(0, 6, 'F 7.01 Rev. 05 Dt. 21.07.2024', 0, 0, 'L');
        } else {
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 6, 'Page ' . $this->getPage(), 0, 0, 'C');
        }
    }
}

$pdf = new ApplicationFormPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Audit Application Form - ' . ($application['org_name'] ?? ''));
$pdf->SetSubject('Audit Application Form F 7.01 Rev. 05');

$pdf->SetMargins(10, 22, 10);
$pdf->SetHeaderMargin(4);
$pdf->SetFooterMargin(6);
$pdf->SetAutoPageBreak(false); // Exact per-page control
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8.2);

$e = function ($key) use ($application) {
    return isset($application[$key]) ? htmlspecialchars((string) $application[$key], ENT_QUOTES, 'UTF-8') : '';
};

$supportAttachments = [
    [
        'label' => 'Process Flow',
        'path_key' => 'process_flow_attachment_path',
        'name_key' => 'process_flow_attachment_name',
    ],
    [
        'label' => 'Organisation Brochure',
        'path_key' => 'organisation_brochure_attachment_path',
        'name_key' => 'organisation_brochure_attachment_name',
    ],
    [
        'label' => 'Customer Specific Requirements',
        'path_key' => 'customer_requirements_attachment_path',
        'name_key' => 'customer_requirements_attachment_name',
    ],
];

$supportAttachments = array_values(array_filter(array_map(function ($attachment) use ($application) {
    $relativePath = trim((string) ($application[$attachment['path_key']] ?? ''));
    if ($relativePath === '') {
        return null;
    }
    $fullPath = str_replace('\\', '/', __DIR__ . '/' . $relativePath);
    if (!@file_exists($fullPath)) {
        return null;
    }
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $type = in_array($extension, ['png', 'jpg', 'jpeg'], true) ? 'image' : ($extension === 'pdf' ? 'pdf' : 'other');
    return [
        'label' => $attachment['label'],
        'name' => trim((string) ($application[$attachment['name_key']] ?? '')) ?: basename($fullPath),
        'path' => $fullPath,
        'extension' => $extension,
        'type' => $type,
    ];
}, $supportAttachments)));

$css = '<style>
    body, td, th, div, span, p { font-family: dejavusans; font-size: 8.6pt; color: #000; line-height: 1.25; }
    .bold { font-weight: bold; }
    .sec-head { font-weight: bold; font-size: 9pt; margin-top: 3px; margin-bottom: 2px; }
    .table-bordered { width: 100%; border-collapse: collapse; }
    .table-bordered td, .table-bordered th { border: 0.5px solid #000; padding: 3px 4px; vertical-align: top; }
    .table-bordered th { background-color: #f5f5f5; font-weight: bold; }
    .sub-head { font-weight: bold; text-decoration: underline; margin-top: 3px; margin-bottom: 2px; }
</style>';

// ==========================================
// PAGE 1: Sections 1, 2, 3, 4
// ==========================================
$pdf->AddPage();
$pdf->SetY(22);

// Section 2 Legal Status
$legalStatus = trim((string) ($application['legal_status'] ?? ''));
$legalStatusDisplay = $legalStatus !== '' ? htmlspecialchars($legalStatus, ENT_QUOTES, 'UTF-8') : 'N. A.';

// Branch offices
$branchOffices = trim((string) ($application['branch_offices'] ?? ''));
$branchDisplay = $branchOffices !== '' ? htmlspecialchars($branchOffices, ENT_QUOTES, 'UTF-8') : 'No';

// Section 3 Cert Type & Standards
$certTypeDisplay = !empty($application['certification_type']) ? htmlspecialchars($application['certification_type'], ENT_QUOTES, 'UTF-8') : 'FIRST CERTIFICATION';
$stdsDisplay = !empty($application['standard_applied']) ? htmlspecialchars($application['standard_applied'], ENT_QUOTES, 'UTF-8') : 'N. A.';
$accredDisplay = !empty($application['accreditation_requirement']) ? htmlspecialchars($application['accreditation_requirement'], ENT_QUOTES, 'UTF-8') : 'NABCB';

// Section 4 Multisite
$multisite = trim((string) ($application['multisite'] ?? ''));
$multiDisplay = $multisite !== '' ? htmlspecialchars($multisite, ENT_QUOTES, 'UTF-8') : 'No';

$multisiteRows = [];
$sitesText = trim((string) ($application['multisite_sites'] ?? ''));
if ($sitesText !== '') {
    $lines = preg_split("/\r\n|\r|\n/", $sitesText);
    foreach ($lines as $ln) {
        $ln = trim($ln);
        if ($ln !== '') {
            $multisiteRows[] = $ln;
        }
    }
}

$p1Html = $css;
$p1Html .= '<div class="sec-head">1. DATA OF ORGANISATION APPLYING FOR CERTIFICATION –</div>';
$p1Html .= '<table class="table-bordered" style="font-size:8.8pt;">'
    . '<tr><td width="35%"><strong>Organization name:</strong></td><td width="65%">' . $e('org_name') . '</td></tr>'
    . '<tr><td height="16mm"><strong>Address of site to be audited:</strong></td><td height="16mm">' . nl2br($e('site_address')) . '</td></tr>'
    . '<tr><td height="16mm"><strong>Office address:</strong></td><td height="16mm">' . nl2br($e('office_address')) . '</td></tr>'
    . '<tr><td><strong>Contact person with designation:</strong></td><td>' . $e('contact_person') . ($application['contact_designation'] ? ' (' . htmlspecialchars($application['contact_designation'], ENT_QUOTES, 'UTF-8') . ')' : '') . '</td></tr>'
    . '<tr><td><strong>Telephone / Mobile:</strong></td><td>' . $e('phone') . '</td></tr>'
    . '<tr><td><strong>E-Mail ID and Website, if any:</strong></td><td>' . $e('email') . ($application['website'] ? ' / ' . htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8') : '') . '</td></tr>'
    . '<tr><td><strong>GSTIN Number:</strong></td><td>' . $e('gstin') . '</td></tr>'
    . '</table>';

$p1Html .= '<div class="sec-head" style="margin-top:3px;">2. COMPANY STRUCTURE –</div>';
$p1Html .= '<table class="table-bordered" style="font-size:8.8pt;">'
    . '<tr><td width="35%"><strong>Legal status :</strong></td><td width="65%">' . $legalStatusDisplay . '</td></tr>'
    . '<tr><td colspan="2" height="13mm">'
    . '<strong>If any branch offices, subsidiary company / Group companies :</strong> ' . $branchDisplay . '<br />'
    . '<em>( If yes, please specify ) :</em> ' . (strcasecmp($branchDisplay, 'Yes') === 0 ? nl2br($e('branch_details')) : 'N. A.')
    . '</td></tr>'
    . '</table>';

$p1Html .= '<div class="sec-head" style="margin-top:3px;">3. CERTIFICATION REQUIREMENT –</div>';
$p1Html .= '<table class="table-bordered" style="font-size:8.8pt;">'
    . '<tr><td width="35%"><strong>Type of certification requested :</strong></td><td width="65%">' . $certTypeDisplay . '</td></tr>'
    . '<tr><td><strong>Standard applied for :</strong></td><td>' . $stdsDisplay . '</td></tr>'
    . '<tr><td><strong>Accreditation requirement :</strong></td><td>' . $accredDisplay . '</td></tr>'
    . '<tr><td colspan="2" height="24mm">'
    . '<strong>Please mention your requirement regarding the Scope / Activity to be certified :</strong><br />'
    . nl2br($e('scope_requirement'))
    . '</td></tr>'
    . '</table>';

$pdf->writeHTML($p1Html, true, false, true, false, '');

// Measure remaining space on Page 1 for Section 4
$currentP1Y = $pdf->GetY();
$availP1Mm = 277 - $currentP1Y;
$siteRowHeightMm = max(14, min(30, round(($availP1Mm - 24) / 3)));

$p1Sec4Html = $css;
$p1Sec4Html .= '<div class="sec-head" style="margin-top:3px;">4. IS YOUR ORGANISATION HAVING MULTISITE? &nbsp;&nbsp; ' . $multiDisplay . '</div>';
if (strcasecmp($multiDisplay, 'Yes') === 0) {
    $p1Sec4Html .= '<div style="font-size:8.4pt; margin-bottom:2px;"><strong>Functions performed centrally :</strong> ' . nl2br($e('multisite_functions')) . '</div>';
}
$p1Sec4Html .= '<div style="font-size:8.4pt; margin-bottom:2px;"><u>List all site/s to be covered :</u></div>';

$p1Sec4Html .= '<table class="table-bordered" style="font-size:8.6pt;">'
    . '<tr>'
    . '<th width="12%" align="center">Site</th>'
    . '<th width="42%" align="center">Address of site/s</th>'
    . '<th width="32%" align="center">Processes/ Operations/ Function</th>'
    . '<th width="14%" align="center">Nº employees</th>'
    . '</tr>';

for ($s = 1; $s <= 3; $s++) {
    $siteAddr = $multisiteRows[$s - 1] ?? '';
    $p1Sec4Html .= '<tr>'
        . '<td align="center" height="' . $siteRowHeightMm . 'mm" style="vertical-align:middle;">Site ' . $s . '</td>'
        . '<td height="' . $siteRowHeightMm . 'mm">' . ($siteAddr !== '' ? nl2br(htmlspecialchars($siteAddr, ENT_QUOTES, 'UTF-8')) : '&nbsp;') . '</td>'
        . '<td height="' . $siteRowHeightMm . 'mm">&nbsp;</td>'
        . '<td height="' . $siteRowHeightMm . 'mm">&nbsp;</td>'
        . '</tr>';
}
$p1Sec4Html .= '<tr><td colspan="4" style="font-size:7.5pt; font-style:italic;">Note : For more sites may attach an annexure with the same formatting as above.</td></tr>';
$p1Sec4Html .= '</table>';

$pdf->writeHTML($p1Sec4Html, true, false, true, false, '');

// ==========================================
// PAGE 2: IMS, Section 5, Section 6
// ==========================================
$pdf->AddPage();
$pdf->SetY(22);

$imsValue = trim((string) ($application['integrated_management_system'] ?? ''));
$isImsYes = (strcasecmp($imsValue, 'Yes') === 0);

$imsLevelsRaw = trim((string) ($application['ims_levels'] ?? ''));
$selectedLevels = [];
if ($imsLevelsRaw !== '') {
    $lines = preg_split("/\r\n|\r|\n/", $imsLevelsRaw);
    foreach ($lines as $ln) {
        $ln = trim($ln);
        if ($ln !== '') {
            $selectedLevels[] = $ln;
        }
    }
}

// Employee matrix calculation
$empRaw = isset($application['employee_structure']) ? $application['employee_structure'] : '';
$empLines = preg_split("/\r\n|\r|\n/", (string) $empRaw);
$deptKeys = [
    'Top management / Administrative staff',
    'Management / Supervisory staff',
    'Operators',
    'Helpers',
    'Others (if any)'
];
$empMatrix = [];
foreach ($deptKeys as $k) {
    $empMatrix[$k] = [
        'gs_perm' => '', 'gs_cont' => '',
        's1_perm' => '', 's1_cont' => '',
        's2_perm' => '', 's2_cont' => '',
        's3_perm' => '', 's3_cont' => ''
    ];
}
if (!empty($empLines)) {
    foreach ($empLines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = explode(' - ', $line, 2);
        if (count($parts) < 2) continue;
        $dept = trim($parts[0]);
        $rest = trim($parts[1]);
        if (!isset($empMatrix[$dept])) {
            $empMatrix[$dept] = [
                'gs_perm' => '', 'gs_cont' => '',
                's1_perm' => '', 's1_cont' => '',
                's2_perm' => '', 's2_cont' => '',
                's3_perm' => '', 's3_cont' => ''
            ];
        }
        $items = explode(';', $rest);
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') continue;
            $pair = explode(':', $item, 2);
            if (count($pair) < 2) continue;
            $label = trim($pair[0]);
            $value = trim($pair[1]);
            switch ($label) {
                case 'General shift - Permanent': $empMatrix[$dept]['gs_perm'] = $value; break;
                case 'General shift - Contract': $empMatrix[$dept]['gs_cont'] = $value; break;
                case 'Shift I - Permanent': $empMatrix[$dept]['s1_perm'] = $value; break;
                case 'Shift I - Contract': $empMatrix[$dept]['s1_cont'] = $value; break;
                case 'Shift II - Permanent': $empMatrix[$dept]['s2_perm'] = $value; break;
                case 'Shift II - Contract': $empMatrix[$dept]['s2_cont'] = $value; break;
                case 'Shift III - Permanent': $empMatrix[$dept]['s3_perm'] = $value; break;
                case 'Shift III - Contract': $empMatrix[$dept]['s3_cont'] = $value; break;
            }
        }
    }
}
$totalEmployees = 0;
foreach ($empMatrix as $deptVals) {
    foreach ($deptVals as $val) {
        if ($val !== '') {
            $totalEmployees += (int) $val;
        }
    }
}

$p2TopHtml = $css;
$p2TopHtml .= '<table class="table-bordered" style="font-size:8.6pt; margin-bottom:3px;">'
    . '<tr><td width="55%"><strong>Is the organisation having an Integrated management system :</strong></td><td width="45%">' . ($isImsYes ? 'Yes' : 'No') . '</td></tr>';

if ($isImsYes && !empty($selectedLevels)) {
    $p2TopHtml .= '<tr><td colspan="2">'
        . '<strong>Selected level/s of integration :</strong><br />';
    foreach ($selectedLevels as $lvl) {
        $p2TopHtml .= '• ' . htmlspecialchars($lvl, ENT_QUOTES, 'UTF-8') . '<br />';
    }
    $p2TopHtml .= '</td></tr>';
}
$p2TopHtml .= '</table>';

$p2TopHtml .= '<div class="sec-head" style="margin-top:2px;">5. FOR THE EMPLOYEE STRUCTURE OF ORGANISATION. <span style="font-weight:normal; font-size:8.2pt;">( Employee information as per each Site and Shift-wise )</span></div>';
$p2TopHtml .= '<div style="font-size:8.2pt; margin-bottom:2px;"><u>Site : 1</u> ( For additional Sites please attach annexure in the same format. )</div>';

$p2TopHtml .= '<table class="table-bordered" style="font-size:7.4pt;">'
    . '<tr style="background:#f2f2f2;">'
    . '<th width="10%" rowspan="3" align="center" style="vertical-align:middle;"></th>'
    . '<th width="22%" rowspan="3" align="center" style="vertical-align:middle;">Department</th>'
    . '<th width="20%" colspan="2" align="center">General shift</th>'
    . '<th width="48%" colspan="6" align="center">IF ORGANISATION IS WORKING UNDER SHIFT</th>'
    . '</tr>'
    . '<tr style="background:#f2f2f2;">'
    . '<th width="20%" colspan="2" align="center"></th>'
    . '<th width="16%" colspan="2" align="center">Shift I</th>'
    . '<th width="16%" colspan="2" align="center">Shift II</th>'
    . '<th width="16%" colspan="2" align="center">Shift III</th>'
    . '</tr>'
    . '<tr style="background:#f2f2f2; font-size:7pt;">'
    . '<th width="10%" align="center" style="padding:1.5px 0.5px;">Permanent</th><th width="10%" align="center">Contract</th>'
    . '<th width="8%" align="center">Per.</th><th width="8%" align="center">Contract</th>'
    . '<th width="8%" align="center">Per.</th><th width="8%" align="center">Contract</th>'
    . '<th width="8%" align="center">Per.</th><th width="8%" align="center">Contract</th>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td width="10%" rowspan="6" align="center" style="vertical-align:middle; font-weight:bold; font-size:7.8pt;">Production/<br />Quality</td>'
    . '<td width="22%">Top management, Administrative staff (for Marketing, HR, Purchase, Design and development, etc.)</td>'
    . '<td width="10%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="10%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8%" align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>Management and Supervisory Staff</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>Operators</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Operators']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>Helpers</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Helpers']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>Others if any ____________</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td align="center" style="font-size:8.2pt; font-weight:bold;">' . htmlspecialchars($empMatrix['Others (if any)']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>Others if any ____________</td>'
    . '<td align="center">&nbsp;</td><td align="center">&nbsp;</td>'
    . '<td align="center">&nbsp;</td><td align="center">&nbsp;</td>'
    . '<td align="center">&nbsp;</td><td align="center">&nbsp;</td>'
    . '<td align="center">&nbsp;</td><td align="center">&nbsp;</td>'
    . '</tr>';

$p2TopHtml .= '<tr>'
    . '<td>&nbsp;</td>'
    . '<td><strong>*Total Nº Employee</strong></td>'
    . '<td colspan="8" align="left" style="font-size:8.6pt; font-weight:bold;">' . ($totalEmployees > 0 ? (int) $totalEmployees : '') . '</td>'
    . '</tr>';
$p2TopHtml .= '</table>';

$p2TopHtml .= '<div style="font-size:7.4pt; font-style:italic; margin-top:2px; margin-bottom:3px;">'
    . 'Note : Personnel involved in the management system are meant partners + employees + collaborators + subcontractors, etc. ( The total staff in the head office + personnel of any sites to be certified + yard personnel if any )'
    . '</div>';

// 4 Questions
$onsiteVal = trim((string) ($application['onsite_installation'] ?? ''));
$onsiteDisplay = $onsiteVal !== '' ? htmlspecialchars($onsiteVal, ENT_QUOTES, 'UTF-8') : 'No';

$partVal = trim((string) ($application['part_time_personnel'] ?? ''));
$partDisplay = $partVal !== '' ? htmlspecialchars($partVal, ENT_QUOTES, 'UTF-8') : 'No';

$excVal = trim((string) ($application['employees_excluded'] ?? ''));
$excDisplay = $excVal !== '' ? htmlspecialchars($excVal, ENT_QUOTES, 'UTF-8') : 'No';

$consVal = trim((string) ($application['consultancy_services_taken'] ?? ''));
$consDisplay = $consVal !== '' ? htmlspecialchars($consVal, ENT_QUOTES, 'UTF-8') : 'No';

$p2TopHtml .= '<table class="table-bordered" style="font-size:8.5pt;">'
    . '<tr><td>'
    . '<strong>On-site installation work or having any other operational yards / temporary sites ? :</strong> ' . $onsiteDisplay . '<br />'
    . '<strong>If yes, Please Explain :</strong> ' . (strcasecmp($onsiteDisplay, 'Yes') === 0 ? nl2br($e('onsite_details')) : 'N. A.')
    . '</td></tr>'
    . '<tr><td>'
    . '<strong>Any part-time personnel ? :</strong> ' . $partDisplay . '<br />'
    . '<strong>If Yes Please specify No of personnel :</strong> ' . (strcasecmp($partDisplay, 'Yes') === 0 ? $e('part_time_count') : 'N. A.')
    . '</td></tr>'
    . '<tr><td>'
    . '<strong>Are there any employees that you did not include in the above-mentioned tables because you consider them to be outside the scope of the audit ? :</strong> ' . $excDisplay . '<br />'
    . '<strong>If yes, Please Explain :</strong> ' . (strcasecmp($excDisplay, 'Yes') === 0 ? nl2br($e('employees_excluded_details')) : 'N. A.')
    . '</td></tr>'
    . '<tr><td>'
    . '<strong>Have any management system consultancy services been taken ? :</strong> ' . $consDisplay . '<br />'
    . '<strong>If yes, by whom ?-</strong> ' . (strcasecmp($consDisplay, 'Yes') === 0 ? nl2br($e('consultancy_details')) : 'N. A.') . '<br />'
    . '<strong>And period of services :</strong> ' . (strcasecmp($consDisplay, 'Yes') === 0 ? nl2br($e('consultancy_period')) : 'N. A.')
    . '</td></tr>'
    . '</table>';

$pdf->writeHTML($p2TopHtml, true, false, true, false, '');

// Section 6
$orgTypeRaw = strtolower(trim((string) ($application['org_type'] ?? '')));
$manuChecked = strpos($orgTypeRaw, 'manufact') !== false;
$serviceChecked = strpos($orgTypeRaw, 'service') !== false;
if ($manuChecked && $serviceChecked) {
    $orgTypeDisplay = 'Manufacturer / Service provider';
} elseif ($manuChecked) {
    $orgTypeDisplay = 'Manufacturer';
} elseif ($serviceChecked) {
    $orgTypeDisplay = 'Service provider';
} else {
    $orgTypeDisplay = !empty($application['org_type']) ? htmlspecialchars($application['org_type'], ENT_QUOTES, 'UTF-8') : 'N. A.';
}

$p2Sec6Html = $css;
$p2Sec6Html .= '<div class="sec-head" style="margin-top:3px;">6. Describe your organization.</div>';
$p2Sec6Html .= '<table class="table-bordered" style="font-size:8.8pt;">'
    . '<tr><td width="35%"><strong>Describe your organization is :</strong></td><td width="65%">' . $orgTypeDisplay . '</td></tr>'
    . '</table>';

$pdf->writeHTML($p2Sec6Html, true, false, true, false, '');

// Measure remaining space on Page 2 for Major customers box
$currentP2YAfterType = $pdf->GetY();
$custHeightMm = max(35, round(276 - $currentP2YAfterType));

$pdf->SetXY(10, $currentP2YAfterType);
$pdf->SetFont('dejavusans', '', 8.6);
$customersContent = '<strong>Major customers :</strong><br />' . nl2br($e('major_customers'));
$pdf->MultiCell(190, $custHeightMm, $customersContent, 1, 'L', 0, 1, 10, $currentP2YAfterType, true, 0, true, true, 0, 'T');

// ==========================================
// PAGE 3: Section 7, Annexure 1, Annexure 2
// ==========================================
$pdf->AddPage();
$pdf->SetY(22);

$otherRaw = isset($application['other_certificates']) ? $application['other_certificates'] : '';
$otherLines = preg_split("/\r\n|\r|\n/", (string) $otherRaw);
$hasFlag = '';
$certRows = [];
if (!empty($otherLines)) {
    foreach ($otherLines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (strpos($line, 'Has other valid certificates:') === 0) {
            $hasFlag = trim(substr($line, strlen('Has other valid certificates:')));
            continue;
        }
        $withoutIndex = preg_replace('/^\d+\.\s*/', '', $line);
        $parts = explode(';', $withoutIndex);
        $row = ['body' => '', 'accred' => '', 'valid' => '', 'number' => ''];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            $pair = explode(':', $part, 2);
            if (count($pair) < 2) continue;
            $label = trim($pair[0]);
            $value = trim($pair[1]);
            switch ($label) {
                case 'Certification body': $row['body'] = $value; break;
                case 'Accreditation': $row['accred'] = $value; break;
                case 'Validity until': $row['valid'] = $value; break;
                case 'Certificate number':
                case 'Certificate(s) number': $row['number'] = $value; break;
            }
        }
        if ($row['body'] !== '' || $row['accred'] !== '' || $row['valid'] !== '' || $row['number'] !== '') {
            $certRows[] = $row;
        }
    }
}
$otherCertFlag = trim((string) ($application['other_certificates_flag'] ?? $hasFlag));
$certDisplay = (stripos($otherCertFlag, 'yes') !== false) ? 'Yes' : 'No';

$p3Html = $css;
$p3Html .= '<div class="sec-head">7. Does the company have any other valid certificate/s ? : &nbsp;' . $certDisplay . '</div>';
$p3Html .= '<table class="table-bordered" style="font-size:8.5pt;">'
    . '<tr style="background:#f2f2f2;">'
    . '<th width="6%" align="center">Sr.</th>'
    . '<th width="32%" align="center">Certification body</th>'
    . '<th width="20%" align="center">Accreditation</th>'
    . '<th width="20%" align="center">Validity until</th>'
    . '<th width="22%" align="center">Certificate/s number</th>'
    . '</tr>';

for ($cr = 0; $cr < 2; $cr++) {
    $row = $certRows[$cr] ?? null;
    $p3Html .= '<tr>'
        . '<td align="center" height="7mm" style="vertical-align:middle;">' . ($cr + 1) . '.</td>'
        . '<td height="7mm">' . ($row ? htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>'
        . '<td height="7mm">' . ($row ? htmlspecialchars($row['accred'], ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>'
        . '<td height="7mm">' . ($row ? htmlspecialchars($row['valid'], ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>'
        . '<td height="7mm">' . ($row ? htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>'
        . '</tr>';
}
$p3Html .= '</table>';

$p3Html .= '<div style="font-size:8.6pt; margin-top:3px;"><u>Date of system implementation</u> : ' . $e('system_implementation_date') . '</div>';
$p3Html .= '<div style="font-size:8.6pt; margin-top:2px; margin-bottom:3px;"><u>Please mention your proposed month-year to conduct the audit</u> : ' . $e('proposed_audit_month') . '</div>';

// Annexure 1 - QMS
$compLog = trim((string) ($application['complicated_logistics'] ?? ''));
$clDisplay = (strcasecmp($compLog, 'Yes') === 0) ? 'Yes' : 'No';
$designResp = trim((string) ($application['design_responsibility'] ?? ''));

$p3Html .= '<div style="text-align:center; font-weight:bold; font-size:9pt; margin-top:3px; margin-bottom:2px;">Annexure 1 - QMS ( Basic Information and for Applicant of 9001 Certification )</div>';
$p3Html .= '<table class="table-bordered" style="font-size:8.6pt;">'
    . '<tr height="11mm"><td height="11mm"><strong>Main products :</strong><br />' . nl2br($e('main_products')) . '</td></tr>'
    . '<tr height="11mm"><td height="11mm"><strong>Main raw materials used or processed :</strong><br />' . nl2br($e('raw_materials')) . '</td></tr>'
    . '<tr height="12.5mm"><td height="12.5mm"><strong>Operations involved in the service/manufacturing of your product :</strong><br />' . nl2br($e('operations')) . '</td></tr>'
    . '<tr height="11mm"><td height="11mm"><strong>Processes that are provided by external sources related to the product/service :</strong><br />' . nl2br($e('external_processes')) . '</td></tr>'
    . '<tr height="11mm"><td height="11mm"><strong>Do you have complicated logistics involving more than one building or location where workis carried out ? :</strong> ' . $clDisplay
    . ($application['complicated_logistics_details'] ? '<br /><em>Details :</em> ' . nl2br(htmlspecialchars($application['complicated_logistics_details'], ENT_QUOTES, 'UTF-8')) : '')
    . '</td></tr>'
    . '<tr><td><strong>Who designs the products/services that you provide to your customers ? :</strong><br />'
    . nl2br(htmlspecialchars($designResp !== '' ? $designResp : 'N. A.', ENT_QUOTES, 'UTF-8'))
    . '</td></tr>'
    . '</table>';

// Annexure 2 - EMS
$p3Html .= '<div style="text-align:center; font-weight:bold; font-size:9pt; margin-top:3px; margin-bottom:2px;">Annexure 2 – EMS ( For Applicant of 14001 Certification )</div>';
$p3Html .= '<table class="table-bordered" style="font-size:8.5pt;">'
    . '<tr height="12mm"><td height="12mm"><strong>Any Statutory Regulatory Requirements related to Environmental clearance for the product manufactured by the organization :</strong><br />' . nl2br($e('ems_statutory_requirements')) . '</td></tr>'
    . '<tr height="12mm"><td height="12mm"><strong>Any Hazardous Raw / Processing materials used :</strong><br />' . nl2br($e('ems_hazardous_materials')) . '</td></tr>'
    . '<tr height="12mm"><td height="12mm"><strong>Any adverse impact identified on the environment due to operations involved in the service/manufacturing processes :</strong><br />' . nl2br($e('ems_adverse_impact')) . '</td></tr>'
    . '<tr height="12mm"><td height="12mm"><strong>is the organisation’s location (s ) particularly environmentally sensitive to any conditions :</strong><br />' . nl2br($e('ems_sensitive_location')) . '</td></tr>'
    . '<tr height="12mm"><td height="12mm"><strong>Any nearby, e.g., archaeological site, listedbuilding, adjacent to sensitive locations, etc. :</strong><br />' . nl2br($e('ems_nearby_sensitive')) . '</td></tr>'
    . '<tr height="12mm"><td height="12mm"><strong>Do you have any history of adverse performance or operations where an emergency condition would result in asignificant environmental event :</strong><br />' . nl2br($e('ems_emergency_history')) . '</td></tr>'
    . '</table>';

$pdf->writeHTML($p3Html, true, false, true, false, '');

// ==========================================
// PAGE 4: Annexure 3, Note, Declaration & Signature, Footer address
// ==========================================
$pdf->AddPage();
$pdf->SetY(22);

$p4TopHtml = $css;
$p4TopHtml .= '<div style="text-align:center; font-weight:bold; font-size:9pt; margin-bottom:3px;">Annexure 3 – OHSMS ( For Applicant of 45001 Certification )</div>';
$p4TopHtml .= '<table class="table-bordered" style="font-size:8.5pt;">'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Statutory Regulatory Requirements related to Health and Safety for the product manufactured by the organization :</strong><br />' . nl2br($e('ohsms_statutory_requirements')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Any Hazardous raw / Processing material, dangerous substances used/ Present at the site :</strong><br />' . nl2br($e('ohsms_hazardous_materials')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Identify the personnel’s health and safety risks due to operations involved in the service/manufacturing Processes :</strong><br />' . nl2br($e('ohsms_health_safety_risks')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Are there activities carried out on-site, temporary sites, or sites not under your control :</strong><br />' . nl2br($e('ohsms_activities_temporary_sites')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Do you have higher than industry average rates of accidents, disease, or absence :</strong><br />' . nl2br($e('ohsms_accident_rates')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Do you have members of the public present atyour sites in large numbers ? (e.g., hospitals, schools, public transport hubs ) :</strong><br />' . nl2br($e('ohsms_public_present')) . '</td></tr>'
    . '<tr height="14.5mm"><td height="14.5mm"><strong>Are you subject to mandatory visits (audits ) bygovernmental regulators :</strong><br />' . nl2br($e('ohsms_mandatory_audits')) . '</td></tr>'
    . '</table>';

$p4TopHtml .= '<table class="table-bordered" style="font-size:8.4pt; margin-top:3px;">'
    . '<tr height="14mm"><td height="14mm">'
    . '<strong>Note –</strong><br />'
    . '➤ Initial audit will be conducted in two stages. Stage 2 audit dates/s will be finalized on the assessment of the stage I audit.<br />'
    . '➤ If available, please attach : Process flow diagram, organisation brochure, customer-specific requirements.'
    . '</td></tr>'
    . '</table>';

// Signature Box using HTML table with 3 divided rows on the right
$signatureImgHtml = '';
if (!empty($application['sign_image_path'])) {
    $imgPath = str_replace('\\', '/', __DIR__ . '/' . $application['sign_image_path']);
    if (@file_exists($imgPath)) {
        $signatureImgHtml = '<div style="text-align:center; margin-bottom:2px;"><img src="' . $imgPath . '" height="36" style="display:block; margin:0 auto;" /></div>';
    }
}
$stampSpacer = empty($signatureImgHtml) ? '<div style="height:14mm;"></div>' : '';
$declText = !empty($application['declaration_confirmed']) ? '<div style="font-size:7.2pt; color:#444;">Declaration: Confirmed online by applicant.</div>' : '';

$p4TopHtml .= '<table class="table-bordered" style="font-size:8.6pt; margin-top:3px;">'
    . '<tr>'
    . '<td rowspan="3" width="58%" align="center" style="text-align:center; padding:4px;">'
    . $signatureImgHtml . $stampSpacer
    . '<strong>Sign with a duly stamped</strong><br />'
    . '<span style="font-size:7.4pt;">( We herewith confirm the completeness and accuracy of the information given above. )</span>'
    . $declText
    . '</td>'
    . '<td width="42%" height="13.5mm" style="vertical-align:middle; padding-left:5px;"><strong>Name :</strong> ' . $e('sign_name') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td width="42%" height="13.5mm" style="vertical-align:middle; padding-left:5px;"><strong>Designation :</strong> ' . $e('sign_designation') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td width="42%" height="13.5mm" style="vertical-align:middle; padding-left:5px;"><strong>Date :</strong> ' . $e('sign_date') . '</td>'
    . '</tr>'
    . '</table>';

// AITF Registered Office Address Box
$p4TopHtml .= '<table class="table-bordered" style="font-size:8.5pt; margin-top:3px;">'
    . '<tr><td style="padding:4px 8px; line-height:1.35;">'
    . '<div style="font-weight:bold; text-decoration:underline; font-size:8.6pt;">FOR COMMUNICATION TO AITF- REGISTERED OFFICE ADDRESS :</div>'
    . '<div style="font-weight:bold; font-size:9pt; margin-top:2px;">A. I. T. F. MANAGEMENT CERTIFICATION LIMITED</div>'
    . '<div>Devi Indrayani Wing B 26, Gat No. 382 +3 +4/1, Talawade, Pune – 411 062. Maharashtra, India.</div>'
    . '<div style="margin-top:2px;"><strong>OFFICE PHONE :</strong> 9422011836 / 37 , &nbsp;<strong>WEB SITE –</strong> www.aitf.co.in</div>'
    . '<div><strong>Communication MAIL ID –</strong> aitfmcl@gmail.com</div>'
    . '<div style="font-weight:bold; margin-top:2px;">( CERTIFICATION MANAGER )</div>'
    . '</td></tr>'
    . '</table>';

$pdf->writeHTML($p4TopHtml, true, false, true, false, '');

// Supplementary attachments (appended on separate pages after page 4)
if (!empty($supportAttachments)) {
    foreach ($supportAttachments as $attachment) {
        $pdf->AddPage();
        $pdf->SetY(22);
        $safeLabel = htmlspecialchars($attachment['label'], ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($attachment['name'], ENT_QUOTES, 'UTF-8');
        $headingHtml = '<div style="font-family:dejavusans; font-size:12pt; font-weight:bold; margin-bottom:4px;">' . $safeLabel . ' Attached</div>'
            . '<div style="font-family:dejavusans; font-size:8.5pt; color:#333; margin-bottom:8px;">Original file: ' . $safeName . '</div>';

        if ($attachment['type'] === 'image') {
            $pdf->writeHTML($headingHtml, true, false, true, false, '');
            $imageInfo = @getimagesize($attachment['path']);
            if ($imageInfo && !empty($imageInfo[0]) && !empty($imageInfo[1])) {
                $mime = strtolower((string) ($imageInfo['mime'] ?? ''));
                if ($mime === 'image/png') {
                    $imageType = 'PNG';
                } elseif ($mime === 'image/gif') {
                    $imageType = 'GIF';
                } else {
                    $imageType = 'JPEG';
                }
                $margins = $pdf->getMargins();
                $x = $margins['left'];
                $y = $pdf->GetY() + 2;
                $maxWidth = $pdf->getPageWidth() - $margins['left'] - $margins['right'];
                $maxHeight = $pdf->getPageHeight() - $y - 12;
                $imgWidth = (float) $imageInfo[0];
                $imgHeight = (float) $imageInfo[1];
                $scale = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
                $drawWidth = $imgWidth * $scale;
                $drawHeight = $imgHeight * $scale;
                $drawX = $x + (($maxWidth - $drawWidth) / 2);
                $pdf->Image($attachment['path'], $drawX, $y, $drawWidth, $drawHeight, $imageType, '', '', false, 300, '', false, false, 1, false, false, true);
            } else {
                $pdf->writeHTML('<div style="font-size:8.5pt;">The attached image could not be previewed in the generated PDF.</div>', true, false, true, false, '');
            }
        } elseif ($attachment['type'] === 'pdf') {
            $pdf->writeHTML(
                $headingHtml
                . '<div style="font-family:dejavusans; font-size:8.5pt; line-height:1.5;">The applicant uploaded this supporting PDF separately. Open the embedded file attachment or check the email attachment to view the original document.</div>',
                true,
                false,
                true,
                false,
                ''
            );
            $pdf->Annotation(18, 50, 8, 8, $attachment['name'], [
                'Subtype' => 'FileAttachment',
                'Name' => 'PushPin',
                'FS' => $attachment['path'],
                'Contents' => $attachment['label'] . ': ' . $attachment['name']
            ]);
        } else {
            $pdf->writeHTML(
                $headingHtml
                . '<div style="font-family:dejavusans; font-size:8.5pt;">This supporting file is attached separately.</div>',
                true,
                false,
                true,
                false,
                ''
            );
        }
    }
}

// Name the file after the client (organization)
$clientNameForFile = preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($application['org_name'] ?? ''));
$clientNameForFile = trim($clientNameForFile, '_');
if ($clientNameForFile === '') {
    $clientNameForFile = (string) $application['id'];
}
$filename = 'Application_form_' . $clientNameForFile . '.pdf';

// Clear any previous output to avoid TCPDF header errors
if (ob_get_length()) {
    ob_end_clean();
}

$pdfContent = $pdf->Output($filename, $outputDest);

if ($outputDest === 'S') {
    return $pdfContent;
}

exit;

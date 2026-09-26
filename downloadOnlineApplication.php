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
    public function Header()
    {
        // Logo (same as other AITF PDFs)
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (@file_exists($image_file)) {
            $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Main title
        $this->SetFont('helvetica', 'B', 17.5);
        $this->SetXY(60, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Sub-title for this form
        $this->SetXY(60, 18);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 25, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetLineStyle(array('width' => 0.65, 'color' => array(0, 0, 0)));
        // Single underline below header
        $this->Line($this->original_lMargin, 29, $this->w - $this->original_rMargin, 29);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}


$pdf = new ApplicationFormPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Audit Application Form');
$pdf->SetSubject('Audit Application Form');

$pdf->SetMargins(PDF_MARGIN_LEFT, 22, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->SetFont('dejavusans', '', 9);
$pdf->AddPage();

$e = function ($key) use ($application) {
    return isset($application[$key]) ? htmlspecialchars($application[$key], ENT_QUOTES, 'UTF-8') : '';
};

$createdAtDisplay = '';
if (!empty($application['created_at'])) {
    try {
        $dt = new DateTimeImmutable($application['created_at'], new DateTimeZone('Asia/Kolkata'));
        $createdAtDisplay = $dt->format('d M Y, h:i A');
    } catch (Exception $e) {
        $createdAtDisplay = $application['created_at'];
    }
}

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

$styleBlock = '<style>
    body { font-family: times; font-size: 11px; }
    .table { width: 100%; border-collapse: collapse; }
    .table td, .table th { border: 0.5px solid #000; padding: 5px; vertical-align: top; }
    .section-title { font-weight: bold; font-size: 11.5px; margin: 6px 0 4px 0; }
    .hr-line { border-top: 1px solid #000; margin: 4px 0 6px 0; }
    .gray { background: #f2f2f2; }
    .green { background: #e9f0e2; }
    .peach { background: #f7efe4; }
    .small { font-size: 9px; }
    .tight td, .tight th { padding: 4px; }
    .emp-table { width: 100%; border-collapse: collapse; }
    .emp-table td, .emp-table th { border: 0.5px solid #000; padding: 5px; vertical-align: middle; }
    .emp-head { font-weight: bold; text-align: center; }
    .emp-note { font-size: 9px; font-style: italic; }
    .emp-shift-bg { background: #f2f2f2; }
    .emp-dept-bg { background: #f7efe4; }
    /* Section 6 spacing to mimic original template */
    .section6 td, .section6 th { padding: 6px 8px; border: 0.5px solid #000; font-size: 11px; }
    .section6 { border-collapse: collapse; }
    </style>';

$html = $styleBlock;

$html .= '<div style="text-align:right; font-size:10px; margin-top:2px;">Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages() . '</div>';

// Section 1 (replicate reference spacing/weights)
$html .= '<div class="section-title">1. DATA OF ORGANISATION APPLYING FOR CERTIFICATION –</div>';
$html .= '<table class="table" style="font-size:11px;">'
    . '<tr><td width="32%" style="height:30px; padding:6px;">Organization name:</td><td width="68%" style="height:30px; padding:6px;">' . $e('org_name') . '</td></tr>'
    . '<tr><td style="height:38px; padding:6px;">Address of site to be audited:</td><td style="height:38px; padding:6px;">' . nl2br($e('site_address')) . '</td></tr>'
    . '<tr><td style="height:38px; padding:6px;">Office address:</td><td style="height:38px; padding:6px;">' . nl2br($e('office_address')) . '</td></tr>'
    . '<tr><td style="height:32px; padding:6px;">Contact person with designation:</td><td style="height:32px; padding:6px;">' . $e('contact_person') . ($application['contact_designation'] ? ' (' . htmlspecialchars($application['contact_designation'], ENT_QUOTES, 'UTF-8') . ')' : '') . '</td></tr>'
    . '<tr><td style="height:28px; padding:6px;">Telephone / Mobile:</td><td style="height:28px; padding:6px;">' . $e('phone') . '</td></tr>'
    . '<tr><td style="height:28px; padding:6px;">E-Mail Id and Web-site if any:</td><td style="height:28px; padding:6px;">' . $e('email') . ($application['website'] ? ' / ' . htmlspecialchars($application['website'], ENT_QUOTES, 'UTF-8') : '') . '</td></tr>'
    . '<tr><td style="height:28px; padding:6px;">GSTIN Number:</td><td style="height:28px; padding:6px;">' . $e('gstin') . '</td></tr>'
    . '</table>';

// Section 2
$html .= '<div class="section-title" style="margin-top:10px;">2. COMPANY STRUCTURE –</div>';
$html .= '<div style="margin-left:4px;">'
    . '<div><u>Legal status:</u> ' . $e('legal_status') . '</div>'
    . '<div style="margin-top:6px;"><u>If any branch offices, subsidiary company / Group companies</u>: ' . $e('branch_offices') . '</div>'
    . '<div style="margin-left:12px; margin-top:3px;">' . nl2br($e('branch_details')) . '</div>'
    . '<div style="margin-top:8px;"><u>Any legal (Statutory and Regulatory) obligations applicable to your product/ service ?</u> ' . $e('legal_obligations_applicable') . '</div>'
    . '<div style="margin-left:12px; margin-top:3px;"><em>(If yes please specify):</em> ' . nl2br($e('legal_obligations')) . '</div>'
    . '</div>';

// Section 3 (text only, no tick marks) — mirror reference spacing
$html .= '<div class="section-title" style="margin-top:10px;">3. CERTIFICATION REQUIREMENT – <span style="font-weight:normal;">Please Tick mark (√/☑ )</span></div>';
$html .= '<table class="table" style="font-size:11px;">'
    . '<tr><td width="35%" style="padding:6px;"><strong>Type of certification requested:</strong></td><td width="65%" style="padding:6px; height:30px;">' . nl2br($e('certification_type')) . '</td></tr>'
    . '<tr><td style="padding:6px;"><strong>Standard applied for:</strong></td><td style="padding:6px; height:26px;">' . nl2br($e('standard_applied')) . '</td></tr>'
    . '<tr><td style="padding:6px;"><strong>Accreditation requirement:</strong></td><td style="padding:6px; height:24px;">' . nl2br($e('accreditation_requirement')) . '</td></tr>'
    . '<tr><td style="padding:6px;"><strong>Please mention your requirement regarding Scope / Activity to be certify:</strong></td><td style="padding:6px; height:32px;">' . nl2br($e('scope_requirement')) . '</td></tr>'
    . '</table>';

// Section 4 (match reference template; dynamic rows = number filled, fallback 3)
$multisiteValue = trim((string) $application['multisite']);
$yesBox = ($multisiteValue === 'Yes') ? '&#9745; Yes' : '&#9744; Yes';
$noBox = ($multisiteValue === 'No') ? '&#9745; No' : '&#9744; No';
$html .= '<div class="section-title" style="margin-top:10px;">4. IS YOUR ORGANISATION HAVING MULTISITE? '
    . '<span style="font-weight:normal; font-size:11px; margin-left:10px;">' . $yesBox . '&nbsp;&nbsp;&nbsp;' . $noBox . '</span>'
    . '</div>';
$html .= '<div style="margin-left:4px; margin-bottom:4px;"><strong>If yes please specify</strong> what functions are performed centrally (eg. Marketing, Purchasing, Top Management, HR, Contract review, Customer Care etc…):- ' . nl2br($e('multisite_functions')) . '</div>';

// Multisite rows based on filled lines; if none, show 3 blank rows
$multisiteRows = [];

$sitesText = trim((string) $application['multisite_sites']);
if ($sitesText !== '') {
    $lines = preg_split("/\r\n|\r|\n/", $sitesText);
    foreach ($lines as $ln) {
        $ln = trim($ln);
        if ($ln !== '') {
            $multisiteRows[] = $ln;
        }
    }
}
// Limit to maximum 2 rows so the table stays on the same page.
// If there is 1 site -> 1 row, 2 or more -> 2 rows, none -> 1 empty row.
if (count($multisiteRows) > 2) {
    $multisiteRows = array_slice($multisiteRows, 0, 2);
}
$rowCount = max(1, count($multisiteRows));

$html .= '<div style="margin-left:4px; margin-bottom:4px;"><strong>List all site/s to be covered</strong> (Please use hierarchal structures i.e. firstly mention central office/Head office then other sites):</div>';
// Table styling adjusted to closely match the original template (image 3)
// nobr="true" keeps the entire table (rows + note) on the same page
$html .= '<table class="table" style="font-size:11px; border-collapse:collapse;" nobr="true">'
    . '<tr>'
    . '<th width="12%" align="center" style="padding:6px 4px;"><strong>Site</strong></th>'
    . '<th width="38%" align="center" style="padding:6px 4px;"><strong>Address of site/s</strong></th>'
    . '<th width="30%" align="center" style="padding:6px 4px;"><strong>Processes/Operations/ Function</strong></th>'
    . '<th width="20%" align="center" style="padding:6px 4px;"><strong>Nº employees</strong></th>'
    . '</tr>';
for ($i = 0; $i < $rowCount; $i++) {
    $addr = $multisiteRows[$i] ?? '';
    $padContent = $addr !== '' ? nl2br(htmlspecialchars($addr, ENT_QUOTES, 'UTF-8')) : '&nbsp;';
    // Fixed-height rows to mimic tall boxes from the reference template
    // Keep this moderate so that the Note row does not jump to the next page
    $rowHeight = 60; // pixels
    $html .= '<tr>'
        . '<td align="center" style="height:' . $rowHeight . 'px; padding:6px 4px; vertical-align:middle; border-bottom:0.5px dotted #000;">Site ' . ($i + 1) . '</td>'
        . '<td style="height:' . $rowHeight . 'px; padding:6px 8px; vertical-align:top; border-bottom:0.5px dotted #000;">' . $padContent . '</td>'
        . '<td style="height:' . $rowHeight . 'px; padding:6px 8px; vertical-align:top; border-bottom:0.5px dotted #000;">&nbsp;</td>'
        . '<td style="height:' . $rowHeight . 'px; padding:6px 8px; vertical-align:top; border-bottom:0.5px dotted #000;">&nbsp;</td>'
        . '</tr>';
}
$html .= '<tr><td colspan="4" class="small" style="border-top:0.5px dotted #000; border-bottom:0.5px dotted #000; padding:4px 6px 4px 6px;">Note : For more sites may attach annexure with the same formatting as above.</td></tr>';
$html .= '</table>';

// Render Sections 1-4, then start Section 5 on a fresh page below the header line
$pdf->writeHTML($html, true, false, true, false, '');
$html = $styleBlock;
$pdf->AddPage();
$pdf->SetY(34);

$html .= '<div class="section-title" style="margin-top:6px;">5. FOR THE EMPLOYEE STRUCTURE OF ORGANISATION. <span style="font-weight:normal;">(Employee information by as per each Site and Shift wise)</span></div>';
$html .= '<div style="margin-bottom:4px;">Site: 1 (For additional Sites please attach annexure in the same format.)</div>';

$empRaw = isset($application['employee_structure']) ? $application['employee_structure'] : '';
$empLines = preg_split("/\r\n|\r|\n/", (string) $empRaw);
$deptKeys = [
    'Top management / Administrative staff',
    'Management / Supervisory staff',
    'Operators',
    'Helpers',
    'Others (if any)'
];

// Initialise matrix with all departments and all shift columns
$empMatrix = [];
foreach ($deptKeys as $k) {
    $empMatrix[$k] = [
        'gs_perm' => '',
        'gs_cont' => '',
        's1_perm' => '',
        's1_cont' => '',
        's2_perm' => '',
        's2_cont' => '',
        's3_perm' => '',
        's3_cont' => ''
    ];
}

if (!empty($empLines)) {
    foreach ($empLines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = explode(' - ', $line, 2);
        if (count($parts) < 2) {
            continue;
        }
        $dept = trim($parts[0]);
        $rest = trim($parts[1]);
        if (!isset($empMatrix[$dept])) {
            $empMatrix[$dept] = [
                'gs_perm' => '',
                'gs_cont' => '',
                's1_perm' => '',
                's1_cont' => '',
                's2_perm' => '',
                's2_cont' => '',
                's3_perm' => '',
                's3_cont' => ''
            ];
        }
        $items = explode(';', $rest);
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $pair = explode(':', $item, 2);
            if (count($pair) < 2) {
                continue;
            }
            $label = trim($pair[0]);
            $value = trim($pair[1]);
            switch ($label) {
                case 'General shift - Permanent':
                    $empMatrix[$dept]['gs_perm'] = $value;
                    break;
                case 'General shift - Contract':
                    $empMatrix[$dept]['gs_cont'] = $value;
                    break;
                case 'Shift I - Permanent':
                    $empMatrix[$dept]['s1_perm'] = $value;
                    break;
                case 'Shift I - Contract':
                    $empMatrix[$dept]['s1_cont'] = $value;
                    break;
                case 'Shift II - Permanent':
                    $empMatrix[$dept]['s2_perm'] = $value;
                    break;
                case 'Shift II - Contract':
                    $empMatrix[$dept]['s2_cont'] = $value;
                    break;
                case 'Shift III - Permanent':
                    $empMatrix[$dept]['s3_perm'] = $value;
                    break;
                case 'Shift III - Contract':
                    $empMatrix[$dept]['s3_cont'] = $value;
                    break;
                default:
                    break;
            }
        }
    }
}

// Compute total employees across all departments and shifts
$totalEmployees = 0;
foreach ($empMatrix as $deptVals) {
    foreach ($deptVals as $val) {
        if ($val !== '') {
            $totalEmployees += (int) $val;
        }
    }
}

$html .= '<table class="emp-table" style="font-size:10px;" nobr="true">'
    . '<tr class="gray">'
    . '<th width="12%" rowspan="3" class="emp-head"></th>'
    . '<th width="20%" rowspan="3" class="emp-head">Department</th>'
    . '<th width="17%" colspan="2" class="emp-head emp-dept-bg">General shift</th>'
    . '<th width="51%" colspan="6" class="emp-head emp-shift-bg">IF ORGANISATION WORKING UNDER SHIFT</th>'
    . '</tr>'
    . '<tr class="gray">'
    . '<th width="17%" colspan="2" class="emp-head emp-dept-bg"></th>'
    . '<th width="17%" colspan="2" class="emp-head emp-shift-bg">Shift I</th>'
    . '<th width="17%" colspan="2" class="emp-head emp-shift-bg">Shift II</th>'
    . '<th width="17%" colspan="2" class="emp-head emp-shift-bg">Shift III</th>'
    . '</tr>'
    . '<tr class="gray">'
    . '<th width="8.5%" class="emp-head emp-dept-bg">Permanent</th><th width="8.5%" class="emp-head emp-dept-bg">Contract</th>'
    . '<th width="8.5%" class="emp-head emp-shift-bg">Per.</th><th width="8.5%" class="emp-head emp-shift-bg">Contract</th>'
    . '<th width="8.5%" class="emp-head emp-shift-bg">Per.</th><th width="8.5%" class="emp-head emp-shift-bg">Contract</th>'
    . '<th width="8.5%" class="emp-head emp-shift-bg">Per.</th><th width="8.5%" class="emp-head emp-shift-bg">Contract</th>'
    . '</tr>'
    . '<tr>'
    . '<td width="12%" rowspan="6" align="center" style="font-weight:bold; padding:6px 4px; writing-mode:vertical-rl; transform:rotate(180deg);">Production/<br/>Quality</td>'
    . '<td width="20%" style="height:34px; padding:6px;">Top management, Administrative staff (for Marketing, HR, Purchase, Design and development, etc...)</td>'
    . '<td width="8.5%" class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td width="8.5%" class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Top management / Administrative staff']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:32px; padding:6px;">Management and Supervisory staff</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Management / Supervisory staff']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:32px; padding:6px;">Operators</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Operators']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:32px; padding:6px;">Helpers</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Helpers']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:32px; padding:6px;">Others (if any) __________</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['gs_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-dept-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['gs_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s1_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s1_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s2_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s2_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s3_perm'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '<td class="emp-shift-bg" align="center">' . htmlspecialchars($empMatrix['Others (if any)']['s3_cont'], ENT_QUOTES, 'UTF-8') . '</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:32px; padding:6px;">Others (if any) __________</td>'
    . '<td class="emp-dept-bg" align="center">&nbsp;</td>'
    . '<td class="emp-dept-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '<td class="emp-shift-bg" align="center">&nbsp;</td>'
    . '</tr>'
    . '<tr>'
    . '<td style="height:28px; padding:6px;">&nbsp;</td>'
    . '<td style="height:28px; padding:6px;"><em>*Total Nº Employee</em></td>'
    . '<td colspan="8" align="left">' . ($totalEmployees > 0 ? (int) $totalEmployees : '') . '</td>'
    . '</tr>'
    . '</table>'
    . '<div class="emp-note" style="margin-top:2px;">Note: Personnel involved in the management system is meant partners + employees + collaborators + subcontractors, etc. (The total staff in head office + personnel of any sites to be certified + yard personnel if any)</div>'
    . '<br />'
    . '<table class="table tight">'
    . '<tr><td width="35%"><strong>Onsite installation work or having any other operational yards / temporary sites?</strong></td><td width="65%">' . $e('onsite_installation') . ($application['onsite_details'] ? ' - ' . nl2br(htmlspecialchars($application['onsite_details'], ENT_QUOTES, 'UTF-8')) : '') . '</td></tr>'
    . '<tr><td><strong>Any part time personnel?</strong></td><td>' . $e('part_time_personnel') . ($application['part_time_count'] ? ' (No. of personnel: ' . htmlspecialchars($application['part_time_count'], ENT_QUOTES, 'UTF-8') . ')' : '') . '</td></tr>'
    . '<tr><td><strong>Employees excluded from audit scope</strong></td><td>' . $e('employees_excluded') . ($application['employees_excluded_details'] ? ' - ' . nl2br(htmlspecialchars($application['employees_excluded_details'], ENT_QUOTES, 'UTF-8')) : '') . '</td></tr>'
    . '</table>';

// Section 6
// Adaptive sizing: when the client fills long text in these free-text fields,
// keep the whole section on the same page (avoid spilling 1-2 lines onto the
// next page and leaving it almost blank). The more text, the smaller the font
// and the tighter the row heights.
$s6Fields = [
    (string) ($application['major_customers'] ?? ''),
    (string) ($application['main_products'] ?? ''),
    (string) ($application['raw_materials'] ?? ''),
    (string) ($application['operations'] ?? ''),
    (string) ($application['external_processes'] ?? ''),
    (string) ($application['design_responsibility'] ?? ''),
];
$s6Len = 0;
foreach ($s6Fields as $s6f) {
    $s6Len += function_exists('mb_strlen') ? mb_strlen($s6f) : strlen($s6f);
}
if ($s6Len > 1200) {
    $s6Font = 7.5;
    $s6RowH = 16;
    $s6Pad = '3px 6px';
} elseif ($s6Len > 700) {
    $s6Font = 8.5;
    $s6RowH = 20;
    $s6Pad = '4px 7px';
} elseif ($s6Len > 350) {
    $s6Font = 9.5;
    $s6RowH = 26;
    $s6Pad = '5px 8px';
} else {
    $s6Font = 11;
    $s6RowH = 34;
    $s6Pad = '6px 8px';
}
$html .= '<div style="height:14px;"></div>';
$html .= '<div class="section-title" style="margin-top:12px;">6. Describe your organization.</div>';
$orgTypeRaw = strtolower(trim((string) $application['org_type']));
$manuChecked = strpos($orgTypeRaw, 'manufact') !== false;
$serviceChecked = strpos($orgTypeRaw, 'service') !== false;
$manuBox = ($manuChecked ? '&#9745;' : '&#9744;') . ' Manufacturer';
$serviceBox = ($serviceChecked ? '&#9745;' : '&#9744;') . ' Service provider';
$s6Cell = 'padding:' . $s6Pad . '; border:0.5px solid #000;';
$html .= '<table class="section6" style="width:100%; border-collapse:collapse; font-size:' . $s6Font . 'px;">'
    . '<tr><td width="35%" style="height:24px; ' . $s6Cell . '"><strong>Describe your organization is:</strong></td><td width="65%" style="height:24px; ' . $s6Cell . '">' . $manuBox . '&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;' . $serviceBox . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Major customers:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . nl2br($e('major_customers')) . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Main products:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . nl2br($e('main_products')) . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Main raw materials used or processed:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . nl2br($e('raw_materials')) . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Operations involved in the service / manufacturing of your product:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . nl2br($e('operations')) . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Processes which are provided by external sources related to the product / service:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . nl2br($e('external_processes')) . '</td></tr>'
    . '<tr><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '"><strong>Design responsibility:</strong></td><td style="height:' . $s6RowH . 'px; ' . $s6Cell . '">' . $e('design_responsibility') . '</td></tr>'
    . '</table>';

// Render everything up to Section 6, then start a fresh page for Section 7
$pdf->writeHTML($html, true, false, true, false, '');
$html = '';
$pdf->AddPage();

// Move the starting cursor below the header underline so Section 7 content appears after the line
$pdf->SetY(34);

// Section 7
$flagValue = isset($application['other_certificates_flag']) ? $application['other_certificates_flag'] : '';
$flagRaw = strtolower(trim((string) $flagValue));
$yesBox = (strpos($flagRaw, 'yes') !== false ? '&#9745;' : '&#9744;') . ' Yes';
$noBox = (strpos($flagRaw, 'no') !== false ? '&#9745;' : '&#9744;') . ' No';
$html .= '<div class="section-title" style="margin-top:10px; font-size:12px; font-weight:bold;">7. Does the company have any other valid certificate/s?: '
    . '<span style="margin-left:8px;">' . $yesBox . '</span>'
    . '<span style="margin-left:14px;">' . $noBox . '</span>'
    . ' <span style="margin-left:16px; text-decoration:underline; font-style:italic;">If yes, please list below.</span>'
    . '</div>';

$otherRaw = isset($application['other_certificates']) ? $application['other_certificates'] : '';
$otherLines = preg_split("/\r\n|\r|\n/", (string) $otherRaw);
$hasFlag = '';
$certRows = [];
if (!empty($otherLines)) {
    foreach ($otherLines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (strpos($line, 'Has other valid certificates:') === 0) {
            $hasFlag = trim(substr($line, strlen('Has other valid certificates:')));
            continue;
        }
        $withoutIndex = preg_replace('/^\d+\.\s*/', '', $line);
        $parts = explode(';', $withoutIndex);
        $row = ['body' => '', 'accred' => '', 'valid' => '', 'number' => ''];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $pair = explode(':', $part, 2);
            if (count($pair) < 2) {
                continue;
            }
            $label = trim($pair[0]);
            $value = trim($pair[1]);
            switch ($label) {
                case 'Certification body':
                    $row['body'] = $value;
                    break;
                case 'Accreditation':
                    $row['accred'] = $value;
                    break;
                case 'Validity until':
                    $row['valid'] = $value;
                    break;
                case 'Certificate number':
                case 'Certificate(s) number':
                    $row['number'] = $value;
                    break;
                default:
                    break;
            }
        }
        if ($row['body'] !== '' || $row['accred'] !== '' || $row['valid'] !== '' || $row['number'] !== '') {
            $certRows[] = $row;
        }
    }
}

$html .= '<table class="table tight" style="font-size:11.5px; border-collapse:collapse; margin-top:8px; border:0.8px solid #000;">';
$html .= '<tr style="background:#e8e2d5;">'
    . '<th width="8%" align="center" style="padding:8px 6px; border:0.8px solid #000;">Sr.</th>'
    . '<th width="28%" align="center" style="padding:8px 6px; border:0.8px solid #000;">Certification body</th>'
    . '<th width="21%" align="center" style="padding:8px 6px; border:0.8px solid #000;">Accreditation</th>'
    . '<th width="21%" align="center" style="padding:8px 6px; border:0.8px solid #000;">Validity until</th>'
    . '<th width="22%" align="center" style="padding:8px 6px; border:0.8px solid #000;">Certificate/s number</th>'
    . '</tr>';
$rowPad = 'padding:10px 8px; height:32px; border:0.8px solid #000;';
if (!empty($certRows)) {
    $sr = 1;
    foreach ($certRows as $row) {
        $html .= '<tr>'
            . '<td align="center" style="' . $rowPad . '">' . $sr . '.</td>'
            . '<td style="' . $rowPad . '">' . htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td style="' . $rowPad . '">' . htmlspecialchars($row['accred'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td style="' . $rowPad . '">' . htmlspecialchars($row['valid'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td style="' . $rowPad . '">' . htmlspecialchars($row['number'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '</tr>';
        $sr++;
    }
} else {
    // Show two rows with default NA when no data is available
    for ($i = 1; $i <= 2; $i++) {
        $html .= '<tr>'
            . '<td align="center" style="' . $rowPad . '">' . $i . '.</td>'
            . '<td style="' . $rowPad . ' text-align:left;">NA</td>'
            . '<td style="' . $rowPad . ' text-align:left;">NA</td>'
            . '<td style="' . $rowPad . ' text-align:left;">NA</td>'
            . '<td style="' . $rowPad . ' text-align:left;">NA</td>'
            . '</tr>';
    }
}
$html .= '</table>'
    . '<div style="margin-top:12px; font-size:11.5px; text-decoration:underline; font-weight:bold;">Date of system implementation:</div>'
    . '<div style="margin:4px 0 10px 0; font-size:11.5px;">' . $e('system_implementation_date') . '</div>'
    . '<div style="margin-top:10px; font-size:11.5px; text-decoration:underline; font-weight:bold;">Please mention your proposed month-year to conduct the audit:</div>'
    . '<div style="margin:4px 0 12px 0; font-size:11.5px;">' . $e('proposed_audit_month') . '</div>'
    . '<table class="table tight" style="font-size:11.5px; border-collapse:collapse; margin-top:6px;">'
    . '<tr><td width="45%" style="padding:8px 6px;"><strong>Consultancy services taken?</strong></td><td width="55%" style="padding:8px 6px;">' . $e('consultancy_services_taken') . '</td></tr>'
    . '<tr><td style="padding:8px 6px;"><strong>If yes, by whom? and period of services:</strong></td><td style="padding:10px 6px;">' . nl2br($e('consultancy_details')) . '</td></tr>'
    . '</table><br />';

$hasSignature = false;
$signatureCellHtml = '';
if (!empty($application['sign_image_path'])) {
    $imgPath = str_replace('\\', '/', __DIR__ . '/' . $application['sign_image_path']);
    if (@file_exists($imgPath)) {
        // Use normal file path in HTML img src so TCPDF can read the image size correctly
        // Specify width and height explicitly to prevent TCPDF from pushing large images to a new page
        $signatureCellHtml .= '<img src="' . $imgPath . '" width="340" height="220" style="display:block; margin: 0 auto;" />';
        $signatureCellHtml .= '<div style="font-size:9px; margin-top: 5px; text-align: center; line-height: 12px;"><strong>Sign with duly stamped</strong><br />(We herewith confirm the completeness and accuracy of the information given above.)</div>';
        $hasSignature = true;
    }
}

if ($hasSignature) {
    $html .= '<div class="section-title">DECLARATION AND SIGNATURE</div>';
    $html .= '<table border="1" cellpadding="3" cellspacing="0" class="form-table" nobr="true">'
        . '<tr>'
        . '<td colspan="2" style="font-size:9px;">'
        . '<strong>Note  </strong><br />'
        . 'Initial audit will be conducted in two stages. Stage 2 audit date(s) will be finalised on assessment of Stage 1 audit.<br />'
        . 'If available, please attach process flow diagram, organisation brochure and any customer specific requirements.'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td width="55%" style="height:115px; vertical-align:bottom; text-align:center;">' . $signatureCellHtml . '</td>'
        . '<td width="45%" style="font-size:11px; vertical-align:middle; padding: 10px;">'
        . '<div style="border: 1px solid #000; padding: 15px; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center;">'
        . '<div style="margin-bottom: 10px;">'
        . '<div><strong>Name:</strong> ' . $e('sign_name') . '</div>'
        . '</div>'
        . '<div style="margin-bottom: 10px;">'
        . '<div><strong>Designation:</strong> ' . $e('sign_designation') . '</div>'
        . '</div>'
        . '<div style="margin-bottom: 10px;">'
        . '<div><strong>Date:</strong> ' . $e('sign_date') . '</div>'
        . '</div>'
        . '<div>'
        . '<div><strong>Declaration:</strong> ' . (!empty($application['declaration_confirmed']) ? 'Confirmed by applicant.' : 'Not confirmed on submission.') . '</div>'
        . '</div>'
        . '</div>'
        . '</td>'
        . '</tr>'
        . '</table>';
} else {
    $html .= '<div class="section-title">DECLARATION</div>';
    $html .= '<table border="1" cellpadding="3" cellspacing="0" class="form-table" nobr="true">'
        . '<tr>'
        . '<td colspan="2" style="font-size:9px;">'
        . '<strong>Note  </strong><br />'
        . 'Initial audit will be conducted in two stages. Stage 2 audit date(s) will be finalised on assessment of Stage 1 audit.<br />'
        . 'If available, please attach process flow diagram, organisation brochure and any customer specific requirements.'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td width="50%" style="font-size:11px; padding: 8px 10px;"><strong>Name:</strong> ' . $e('sign_name') . '</td>'
        . '<td width="50%" style="font-size:11px; padding: 8px 10px;"><strong>Designation:</strong> ' . $e('sign_designation') . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td width="50%" style="font-size:11px; padding: 8px 10px;"><strong>Date:</strong> ' . $e('sign_date') . '</td>'
        . '<td width="50%" style="font-size:11px; padding: 8px 10px;"><strong>Declaration:</strong> ' . (!empty($application['declaration_confirmed']) ? 'Confirmed by applicant.' : 'Not confirmed on submission.') . '</td>'
        . '</tr>'
        . '</table>';
}

$pdf->writeHTML($html, true, false, true, false, '');

if (!empty($supportAttachments)) {
    foreach ($supportAttachments as $attachment) {
        $pdf->AddPage();
        $pdf->SetY(34);
        $safeLabel = htmlspecialchars($attachment['label'], ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($attachment['name'], ENT_QUOTES, 'UTF-8');
        $headingHtml = '<div style="font-family:dejavusans; font-size:14px; font-weight:bold; margin-bottom:6px;">' . $safeLabel . ' Attached</div>'
            . '<div style="font-family:dejavusans; font-size:10px; color:#333; margin-bottom:10px;">Original file: ' . $safeName . '</div>';

        if ($attachment['type'] === 'image') {
            $pdf->writeHTML($headingHtml, true, false, true, false, '');
            $imageInfo = @getimagesize($attachment['path']);
            if ($imageInfo && !empty($imageInfo[0]) && !empty($imageInfo[1])) {
                $margins = $pdf->getMargins();
                $x = $margins['left'];
                $y = $pdf->GetY() + 2;
                $maxWidth = $pdf->getPageWidth() - $margins['left'] - $margins['right'];
                $maxHeight = $pdf->getPageHeight() - $y - $pdf->getBreakMargin() - 4;
                $imgWidth = (float) $imageInfo[0];
                $imgHeight = (float) $imageInfo[1];
                $scale = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);
                $drawWidth = $imgWidth * $scale;
                $drawHeight = $imgHeight * $scale;
                $drawX = $x + (($maxWidth - $drawWidth) / 2);
                $imageType = in_array($attachment['extension'], ['jpg', 'jpeg'], true) ? 'JPEG' : 'PNG';
                $pdf->Image($attachment['path'], $drawX, $y, $drawWidth, $drawHeight, $imageType, '', '', false, 300, '', false, false, 1, false, false, true);
            } else {
                $pdf->writeHTML('<div style="font-size:10px;">The attached image could not be previewed in the generated PDF.</div>', true, false, true, false, '');
            }
        } elseif ($attachment['type'] === 'pdf') {
            $pdf->writeHTML(
                $headingHtml
                . '<div style="font-family:dejavusans; font-size:10px; line-height:1.6;">The applicant uploaded this supporting PDF separately. Open the embedded file attachment or check the email attachment to view the original document.</div>',
                true,
                false,
                true,
                false,
                ''
            );
            $pdf->Annotation(18, 58, 8, 8, $attachment['name'], [
                'Subtype' => 'FileAttachment',
                'Name' => 'PushPin',
                'FS' => $attachment['path'],
                'Contents' => $attachment['label'] . ': ' . $attachment['name']
            ]);
        } else {
            $pdf->writeHTML(
                $headingHtml
                . '<div style="font-family:dejavusans; font-size:10px;">This supporting file is attached separately.</div>',
                true,
                false,
                true,
                false,
                ''
            );
        }
    }
}

// Name the file after the client (organization) instead of the numeric id.
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


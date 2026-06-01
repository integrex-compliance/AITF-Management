<?php
session_start();
require_once 'tcpdf_include.php';
require_once 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$submission = null;

if ($id > 0 && isset($db)) {
    try {
        $stmt = $db->prepare("SELECT * FROM information_updation_forms WHERE id = :id");
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

function infoUpdH($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function infoUpdTick($checked)
{
    return $checked ? '<span style="font-family: dejavusans; font-size: 12pt; font-weight: bold;">&#10003;</span>' : '';
}

function infoUpdParseSiteAdditionRows($details)
{
    $rows = [];
    $lines = preg_split("/\r\n|\n|\r/", (string)$details);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if (preg_match('/^\s*\d+\)\s*Name\/Type:\s*(.*?)\s*\|\s*Address:\s*(.*?)\s*\|\s*Activity:\s*(.*?)\s*\|\s*No\.?\s*Staff:\s*(.*?)\s*$/i', $line, $m)) {
            $rows[] = [
                'name' => trim($m[1]),
                'address' => trim($m[2]),
                'activity' => trim($m[3]),
                'staff' => trim($m[4]),
            ];
        }
    }
    return $rows;
}

function infoUpdParseEmployeeBlocks($details)
{
    $blocks = [];
    $current = null;
    $lines = preg_split("/\r\n|\n|\r/", (string)$details);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if (preg_match('/^Site\s+(\d+)\s+Name\/Type:\s*(.*)$/i', $line, $m)) {
            $idx = max(0, ((int)$m[1]) - 1);
            $current = $idx;
            if (!isset($blocks[$idx])) {
                $blocks[$idx] = [
                    'site' => trim($m[2]),
                    'administration' => [],
                    'design' => [],
                    'production' => [],
                ];
            } else {
                $blocks[$idx]['site'] = trim($m[2]);
            }
            continue;
        }

        if ($current === null) {
            continue;
        }

        $deptKey = null;
        if (stripos($line, 'Administration') === 0) {
            $deptKey = 'administration';
        } elseif (stripos($line, 'Design') === 0) {
            $deptKey = 'design';
        } elseif (stripos($line, 'Production') === 0) {
            $deptKey = 'production';
        }

        if ($deptKey === null) {
            continue;
        }

        if (preg_match('/^.*?:\s*GS\(Per=([^,]*),\s*Cas=([^\)]*)\),\s*Shift\s*I\(Per=([^,]*),\s*Cas=([^\)]*)\),\s*Shift\s*II\(Per=([^,]*),\s*Cas=([^\)]*)\),\s*Shift\s*III\(Per=([^,]*),\s*Cas=([^\)]*)\)\s*$/i', $line, $m)) {
            $blocks[$current][$deptKey] = [
                'gs_per' => trim($m[1]),
                'gs_cas' => trim($m[2]),
                's1_per' => trim($m[3]),
                's1_cas' => trim($m[4]),
                's2_per' => trim($m[5]),
                's2_cas' => trim($m[6]),
                's3_per' => trim($m[7]),
                's3_cas' => trim($m[8]),
            ];
        }
    }

    ksort($blocks);
    return array_values($blocks);
}

class InfoUpdationFormPDF extends TCPDF
{
    public function Header()
    {
        $image_file = K_PATH_IMAGES . 'aitf.jpg';
        if (@file_exists($image_file)) {
            $this->Image($image_file, 17, 5, 43, '', 'JPEG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $this->SetTextColor(11, 42, 122);
        $this->SetFont('times', 'B', 18);
        $this->SetXY(63, 10);
        $this->Cell(0, 30, 'AITF MANAGEMENT CERTIFICATION LIMITED', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetXY(63, 18);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('times', 'BU', 14);
        $this->Cell(0, 25, 'INFORMATION UPDATION FORM', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetLineStyle(['width' => 0.65, 'color' => [0, 0, 0]]);
        $this->Line($this->original_lMargin, 29, $this->w - $this->original_rMargin, 29);
    }
}

// Create PDF
$pdf = new InfoUpdationFormPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('AITF Management Certification Limited');
$pdf->SetAuthor('AITF');
$pdf->SetTitle('Information Updation Form');
$pdf->SetSubject('Information Updation Form');

// Header on, footer off
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 35, 15);
$pdf->SetHeaderMargin(8);
$pdf->SetAutoPageBreak(TRUE, 15);

$sigPath = isset($submission['signature_image']) ? (string)$submission['signature_image'] : '';
$sigPathFs = $sigPath !== '' ? str_replace('\\', '/', $sigPath) : '';
$sigExt = $sigPath !== '' ? strtolower(pathinfo($sigPath, PATHINFO_EXTENSION)) : '';
$sigExists = $sigPath !== '' && $sigExt !== 'pdf' && @file_exists($sigPath);

$changesPage1 = [
    ['label' => 'Company name', 'change' => 'company_name_change', 'details' => 'company_name_details'],
    ['label' => 'Change of organisation ownership', 'change' => 'ownership_change', 'details' => 'ownership_details'],
    ['label' => 'Change of Legal Status (Ltd / Pvt Ltd etc.)', 'change' => 'legal_status_change', 'details' => 'legal_status_details'],
    ['label' => 'Change of Address/location/s has been certified', 'change' => 'address_change', 'details' => 'address_details'],
    ['label' => 'Change in office / Communication address', 'change' => 'office_address_change', 'details' => 'office_address_details'],
    ['label' => 'Telephone, Mobile', 'change' => 'phone_change', 'details' => 'phone_change_details'],
    ['label' => 'E-Mail , Web-site', 'change' => 'email_change', 'details' => 'email_change_details'],
    ['label' => 'Contact person', 'change' => 'contact_person_change', 'details' => 'contact_person_change_details'],
    ['label' => 'Change of certified activity (addition/Reduction) i.e. Scope of the audit', 'change' => 'activity_change', 'details' => 'activity_details']
];

$pdf->AddPage();
$pdf->SetFont('times', '', 11);

$page1 = '
<style>
    .fieldline { font-family: times; font-size: 11pt; line-height: 1.35; }
    .para { font-family: times; font-size: 11pt; line-height: 1.35; }
    .small { font-family: times; font-size: 10pt; line-height: 1.35; }
    .labelu { font-weight: bold; text-decoration: underline; }
    .details { font-family: times; font-size: 10.5pt; margin-top: 2mm; margin-left: 5mm; }
    table.grid { width:100%; border-collapse: collapse; }
    table.grid th { border: 1px solid #000; padding: 1.5mm; font-family: times; font-size: 10pt; text-align: left; font-weight: normal; }
    table.grid td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 1.5mm; font-family: times; font-size: 10pt; height: 10mm; }
    tr.grid-row td { border-bottom: 1px dashed #000; }
    tr.grid-last td { border-bottom: 1px solid #000; }
</style>
<div style="height:1mm;"></div>
<div class="fieldline"><span class="labelu">Name of certified organisation:</span> ' . infoUpdH($submission['org_name'] ?? '') . '</div>
<div style="height:8mm;"></div>
<div class="fieldline"><span class="labelu">Certificate number:</span> ' . infoUpdH($submission['certificate_number'] ?? '') . '</div>
<div style="height:10mm;"></div>
<div class="para"><strong>This form need to be filled before next periodical/surveillance audit for changes made in your organisation since from last certified by AITF.</strong></div>
<div style="height:10mm;"></div>
<div class="para"><strong>Method of Filling:</strong> all the fields in sections 1, 2, 3, 4, 5 and 6 are to be completed, use tick Mark (<span style="font-family: dejavusans;">&#8730;/&#9745;</span>) whereas appropriate, where not applicable put N.A. or &ldquo; &ndash; &rdquo;</div>
<div style="height:10mm;"></div>
<div class="para"><span class="labelu">Review of change in data of organisation -</span></div>
<div style="height:2mm;"></div>
';

foreach ($changesPage1 as $change) {
    $hasChange = isset($submission[$change['change']]) && $submission[$change['change']] === 'Yes';
    $page1 .= '<div class="para" style="margin-top:6mm;">'
        . '<span class="labelu">' . infoUpdH($change['label']) . ':</span> ';
    if (!$hasChange) {
        $page1 .= 'No change ' . infoUpdTick(true);
    } else {
        $details = trim((string)($submission[$change['details']] ?? ''));
        $page1 .= ($details !== '' ? nl2br(infoUpdH($details)) : '&nbsp;');
    }
    $page1 .= '</div>';
}

$siteChange = isset($submission['site_addition_change']) && $submission['site_addition_change'] === 'Yes';
$siteRows = $siteChange ? infoUpdParseSiteAdditionRows($submission['site_addition_details'] ?? '') : [];
$page1 .= '
<div class="para" style="margin-top:6mm;">
    <span class="labelu">Addition of site/s to be covered under the audit scope</span>
    <span class="small">(other sites to be certified and/or operational sites and/or external works, for additional details please attach annexure in the same format.)</span>
    : ' . ($siteChange ? '&nbsp;' : 'No change ' . infoUpdTick(true)) . '
</div>
<div style="height:2mm;"></div>
<table class="grid" cellpadding="0" cellspacing="0" nobr="true">
    <tr>
        <th style="width:26%;">Name or type</th>
        <th style="width:36%;">Address of Site/s</th>
        <th style="width:30%;">Activity to be carried-out</th>
        <th style="width:8%;">N&deg; staff</th>
    </tr>
    <tr class="grid-row"><td>' . infoUpdH($siteRows[0]['name'] ?? '') . '</td><td>' . infoUpdH($siteRows[0]['address'] ?? '') . '</td><td>' . infoUpdH($siteRows[0]['activity'] ?? '') . '</td><td>' . infoUpdH($siteRows[0]['staff'] ?? '') . '</td></tr>
    <tr class="grid-row"><td>' . infoUpdH($siteRows[1]['name'] ?? '') . '</td><td>' . infoUpdH($siteRows[1]['address'] ?? '') . '</td><td>' . infoUpdH($siteRows[1]['activity'] ?? '') . '</td><td>' . infoUpdH($siteRows[1]['staff'] ?? '') . '</td></tr>
    <tr class="grid-row"><td>' . infoUpdH($siteRows[2]['name'] ?? '') . '</td><td>' . infoUpdH($siteRows[2]['address'] ?? '') . '</td><td>' . infoUpdH($siteRows[2]['activity'] ?? '') . '</td><td>' . infoUpdH($siteRows[2]['staff'] ?? '') . '</td></tr>
    <tr class="grid-last"><td>' . infoUpdH($siteRows[3]['name'] ?? '') . '</td><td>' . infoUpdH($siteRows[3]['address'] ?? '') . '</td><td>' . infoUpdH($siteRows[3]['activity'] ?? '') . '</td><td>' . infoUpdH($siteRows[3]['staff'] ?? '') . '</td></tr>
</table>
';

$pdf->writeHTML($page1, true, false, true, false, '');

$pdf->AddPage();
$pdf->SetFont('times', '', 11);

$employeeChange = isset($submission['employee_change']) && $submission['employee_change'] === 'Yes';
$employeeBlocks = $employeeChange ? infoUpdParseEmployeeBlocks($submission['employee_details'] ?? '') : [];
$emp1 = $employeeBlocks[0] ?? ['site' => '', 'administration' => [], 'design' => [], 'production' => []];
$emp2 = $employeeBlocks[1] ?? ['site' => '', 'administration' => [], 'design' => [], 'production' => []];
$consultancyYes = isset($submission['consultancy_services']) && $submission['consultancy_services'] === 'Yes';
$consultancyNo = isset($submission['consultancy_services']) && $submission['consultancy_services'] === 'No';

$page2 = '
<style>
    .para { font-family: times; font-size: 11pt; line-height: 1.35; }
    .small { font-family: times; font-size: 10pt; line-height: 1.35; }
    .tiny { font-family: times; font-size: 9pt; line-height: 1.25; }
    .labelu { font-weight: bold; text-decoration: underline; }
    table.emp { width:100%; border-collapse: collapse; }
    table.emp th, table.emp td { border: 1px solid #000; padding: 1.6mm; font-family: times; font-size: 9pt; }
    table.emp th { text-align: center; font-weight: bold; }
    table.emp td { height: 7mm; }
    table.meta { width: 100%; border-collapse: collapse; }
    table.meta td { border: 1px solid #000; padding: 2mm; font-family: times; font-size: 9pt; }
    .details { font-family: times; font-size: 10.5pt; margin-top: 2mm; margin-left: 5mm; }
    table.decl { width:100%; border-collapse: collapse; border: 1px solid #000; }
    table.decl td { border: 1px solid #000; font-family: times; font-size: 10pt; padding: 3mm; }
</style>
<div class="para">
    <span class="labelu">Change of number of employees (addition/Reduction):</span>
    ' . ($employeeChange ? '&nbsp;' : 'No change ' . infoUpdTick(true)) . '
</div>
<div class="para small">If there are changes please split- up employee information as per following chart, Location (Unit)/ Shifts wise:</div>
<div style="height:2mm;"></div>
<table class="emp" cellpadding="0" cellspacing="0" nobr="true">
    <tr>
        <th style="width:12%;">Site/s</th>
        <th style="width:18%;">Department</th>
        <th style="width:10%;">General Shift<br><span class="tiny">Per.</span></th>
        <th style="width:10%;">General Shift<br><span class="tiny">Cas.</span></th>
        <th style="width:10%;">Shift I<br><span class="tiny">Per.</span></th>
        <th style="width:10%;">Shift I<br><span class="tiny">Cas.</span></th>
        <th style="width:10%;">Shift II<br><span class="tiny">Per.</span></th>
        <th style="width:10%;">Shift II<br><span class="tiny">Cas.</span></th>
        <th style="width:10%;">Shift III<br><span class="tiny">Per.</span></th>
        <th style="width:10%;">Shift III<br><span class="tiny">Cas.</span></th>
    </tr>
    <tr>
        <td rowspan="3" style="vertical-align:top;"><strong>Name or type:</strong><br>' . infoUpdH($emp1['site'] ?? '') . '</td>
        <td><strong>Administration</strong><br><span class="tiny">(Inc. Mktg. / Others)</span></td>
        <td>' . infoUpdH($emp1['administration']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp1['administration']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td><strong>Design</strong></td>
        <td>' . infoUpdH($emp1['design']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp1['design']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td><strong>Production</strong><br><span class="tiny">(Inc. Quality)</span></td>
        <td>' . infoUpdH($emp1['production']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp1['production']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td rowspan="3" style="vertical-align:top;"><strong>Name or type:</strong><br>' . infoUpdH($emp2['site'] ?? '') . '</td>
        <td><strong>Administration</strong><br><span class="tiny">(Inc. Mktg. / Others)</span></td>
        <td>' . infoUpdH($emp2['administration']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp2['administration']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td><strong>Design</strong></td>
        <td>' . infoUpdH($emp2['design']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp2['design']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td><strong>Production</strong><br><span class="tiny">(Inc. Quality)</span></td>
        <td>' . infoUpdH($emp2['production']['gs_per'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['gs_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s1_per'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s1_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s2_per'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s2_cas'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s3_per'] ?? '') . '</td><td>' . infoUpdH($emp2['production']['s3_cas'] ?? '') . '</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center; font-weight:bold;">Total N&deg; Employee</td>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
</table>
';

$page2 .= '
<div style="height:3mm;"></div>
<div class="para small">For additional sites please attach annexure in the same format.</div>
<div style="height:3mm;"></div>
<div class="para">
    Have consultancy services or in-house training been performed after certified by AITF on the subject of ISO 9001:
    Yes ' . infoUpdTick($consultancyYes) . '&nbsp;&nbsp;&nbsp; no ' . infoUpdTick($consultancyNo) . '
</div>
<div class="para" style="margin-top:2mm;"><span class="labelu">If yes, by whom ?</span> ' . ($consultancyYes ? infoUpdH($submission['consultancy_details'] ?? '') : '&nbsp;') . '</div>
<div style="height:3mm;"></div>
<div class="para"><strong>Note &ndash;</strong></div>
<div class="para" style="margin-left:5mm;">&#8226; Surveillance manday will be decided with considering present information of the organisation status.</div>
<div class="para" style="margin-left:5mm;">&#8226; Any change in organisation production/Service realisation Process flow, organisation brochure, and customer specific requirements, please enclose with this form.</div>
<div style="height:3mm;"></div>
<div class="para"><strong>We herewith confirm the completeness and accuracy of the change of information given above.</strong></div>
<div style="height:2mm;"></div>
<table class="decl" cellpadding="0" cellspacing="0" nobr="true">
    <tr>
        <td style="width:55%; height:42mm; text-align:center; vertical-align:top;">
            <div style="margin-top:3mm; font-size: 10pt;">Sign with duly stamped</div>
            ' . ($sigExists ? '<div style="margin-top:6mm;"><img src="' . $sigPathFs . '" style="width:60mm; height:auto;"></div>' : '<div style="margin-top:28mm;">&nbsp;</div>') . '
        </td>
        <td style="width:45%; padding:0;">
            <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="border-bottom:1px solid #000; padding:3mm 3mm 2.5mm 3mm; height:14mm; vertical-align:middle;">
                        <span class="labelu">Name:</span> ' . infoUpdH($submission['sign_name'] ?? '') . '
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #000; padding:3mm 3mm 2.5mm 3mm; height:14mm; vertical-align:middle;">
                        <span class="labelu">Designation:</span> ' . infoUpdH($submission['sign_designation'] ?? '') . '
                    </td>
                </tr>
                <tr>
                    <td style="padding:3mm 3mm 2.5mm 3mm; height:14mm; vertical-align:middle;">
                        <span class="labelu">Date:</span> ' . infoUpdH($submission['sign_date'] ?? '') . '
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
';

$page2 .= '
<div style="height:3mm;"></div>
<table style="width:100%; border:1px solid #000;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding:3mm; font-family: times; font-size: 9.5pt;">
            <strong>FOR COMMUNICATION TO AITF- REGISTERED OFFICE ADDRESS:</strong><br>
            <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong><br>
            Devi Indrayani Wing B 26, Gat No. 382+3+4/1, Talawade, Pune &ndash; 411 062. Maharashtra, India.<br>
            <strong>OFFICE PHONE</strong> &#9742; 020 65116677, <strong>WEB SITE</strong> &ndash; www.aitf.co.in<br>
            <strong>Communication MAIL ID</strong> &ndash; aitfmcl@gmail.com (CERTIFICATION MANAGER)
        </td>
    </tr>
</table>
<div style="height:2mm;"></div>
<table style="width:100%; border:1px solid #000;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding:3mm; font-family: times; font-size: 10pt;">
            <strong>FOR AITF REVIEW :</strong>
        </td>
    </tr>
    <tr>
        <td style="height:35mm; border-top:1px solid #000; font-family: times; font-size: 9pt; vertical-align:top; padding:3mm;">
            <div style="font-family: times; font-size: 9pt; font-style: italic; line-height: 1.35; text-align: justify;">
                ' . (trim((string)($submission['aitf_review'] ?? '')) !== '' ? nl2br(infoUpdH($submission['aitf_review'])) : '&nbsp;') . '
            </div>
            <div style="height:8mm;"></div>
            <div style="text-align:right;">
                Sign of reviewer &nbsp;&nbsp;(AITF)
            </div>
        </td>
    </tr>
</table>
<div style="height:3mm;"></div>
<table class="meta" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:22%;"><strong>Form No. F 7.31</strong></td>
        <td style="width:28%;"><strong>Rev.:</strong>00, <strong>Date:</strong> 01.10.2013</td>
        <td style="width:28%;"><strong>Issue.:</strong> 01, <strong>Date:</strong> 01.10.2013</td>
        <td style="width:22%;"><strong>Page:</strong> 1 of 1</td>
    </tr>
</table>
';

$pdf->writeHTML($page2, true, false, true, false, '');

// Output PDF
$filename = 'Information_Updation_Form_' . $submission['org_name'] . '_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D'); // 'D' = force download
?>

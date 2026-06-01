<?php
session_start();
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Information Updation Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .no-print button {
            padding: 12px 30px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
            font-weight: bold;
        }
        
        .btn-print {
            background: #1e3a8a;
            color: #fff;
        }
        
        .btn-close {
            background: #6b7280;
            color: #fff;
        }
        
        /* Form Meta Table */
        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        
        table.meta td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 9pt;
        }
        
        /* Header */
        .header {
            border: 2px solid #000;
            text-align: center;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18pt;
            margin: 0 0 5px 0;
            color: #1e3a8a;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 14pt;
            margin: 0;
            text-decoration: underline;
        }
        
        /* Fields */
        .field {
            margin-bottom: 10px;
            font-size: 10.5pt;
        }
        
        .field strong {
            font-weight: bold;
        }
        
        .intro {
            margin: 15px 0;
            font-size: 10pt;
            line-height: 1.5;
        }
        
        .method {
            margin: 12px 0;
            font-size: 9.5pt;
            font-style: italic;
            line-height: 1.5;
        }
        
        /* Section Title */
        h3 {
            font-size: 11pt;
            text-decoration: underline;
            margin: 15px 0 10px 0;
        }
        
        /* Change Items */
        .change-row {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .change-row:last-child {
            border-bottom: none;
        }
        
        .change-label {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        
        .checkbox {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #000;
            margin: 0 3px;
            text-align: center;
            line-height: 12px;
            vertical-align: middle;
        }
        
        .checkbox.checked::before {
            content: '✓';
            font-weight: bold;
            font-size: 11pt;
        }
        
        .details {
            margin-top: 8px;
            padding: 8px;
            background: #f5f5f5;
            border-left: 3px solid #666;
            font-size: 9.5pt;
        }
        
        /* Note */
        .note {
            margin-top: 15px;
            font-size: 9.5pt;
        }
        
        .note ul {
            margin: 5px 0 0 20px;
        }
        
        .note li {
            margin-bottom: 3px;
        }
        
        /* Signature */
        .signature {
            margin-top: 15px;
            border: 2px solid #000;
            padding: 10px;
        }
        
        .signature > p {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        
        table.sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table.sig-table td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
            font-size: 10pt;
        }
        
        table.sig-table strong {
            display: block;
            margin-bottom: 4px;
        }
        
        .sig-img {
            max-width: 180px;
            max-height: 70px;
            margin-top: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 12px;
            border: 2px solid #000;
            padding: 10px;
        }
        
        .footer strong {
            display: block;
            margin-bottom: 6px;
            font-size: 10pt;
        }
        
        .footer p {
            margin: 3px 0;
            font-size: 9.5pt;
            line-height: 1.4;
        }
        
        .review-box {
            height: 80px;
            border: 1px solid #999;
            margin-top: 8px;
            position: relative;
            background: #fafafa;
        }
        
        .review-box p {
            position: absolute;
            bottom: 8px;
            right: 12px;
            font-size: 9pt;
            font-style: italic;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            @page {
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">Print / Save as PDF</button>
            <button class="btn-close" onclick="window.close()">Close</button>
        </div>
        
        <!-- Header -->
        <div class="header">
            <table style="width: 100%; border: none; margin: 0; padding: 0;">
                <tr>
                    <td style="width: 120px; text-align: center; vertical-align: middle; border: none; padding: 5px;">
                        <img src="aitfImages/newlogo.png" alt="AITF Logo" style="max-width: 100px; height: auto;">
                    </td>
                    <td style="text-align: center; vertical-align: middle; border: none; padding: 5px;">
                        <h1 style="margin: 0; padding: 0;">AITF MANAGEMENT CERTIFICATION LIMITED</h1>
                        <h2 style="margin: 5px 0 0 0; padding: 0;">INFORMATION UPDATION FORM</h2>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Organization Info -->
        <div class="field">
            <strong>Name of certified organisation:</strong> <?php echo htmlspecialchars($submission['org_name'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        
        <div class="field">
            <strong>Certificate number:</strong> <?php echo htmlspecialchars($submission['certificate_number'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        
        <p class="intro">
            <strong>This form need to be filled before next periodical/surveillance audit for changes made in your organisation since from last certified by AITF.</strong>
        </p>
        
        <p class="method">
            <strong>Method of Filling:</strong> all the fields in sections 1, 2, 3, 4, 5 and 6 are to be completed, use tick Mark (√/☑) whereas appropriate, where not applicable put N.A. or " – "
        </p>
        
        <!-- Changes Section -->
        <h3>Review of change in data of organisation -</h3>
        
        <?php
        $changes = [
            ['label' => 'Company name', 'change' => 'company_name_change', 'details' => 'company_name_details'],
            ['label' => 'Change of organisation ownership', 'change' => 'ownership_change', 'details' => 'ownership_details'],
            ['label' => 'Change of Legal Status (Ltd / Pvt Ltd etc.)', 'change' => 'legal_status_change', 'details' => 'legal_status_details'],
            ['label' => 'Change of Address/location/s has been certified', 'change' => 'address_change', 'details' => 'address_details'],
            ['label' => 'Addition of site/s to be covered under the audit scope', 'change' => 'site_addition_change', 'details' => 'site_addition_details'],
            ['label' => 'Change in office / Communication address', 'change' => 'office_address_change', 'details' => 'office_address_details'],
            ['label' => 'Telephone, Mobile', 'change' => 'phone_change', 'details' => 'phone_change_details'],
            ['label' => 'E-Mail , Web-site', 'change' => 'email_change', 'details' => 'email_change_details'],
            ['label' => 'Contact person', 'change' => 'contact_person_change', 'details' => 'contact_person_change_details'],
            ['label' => 'Change of certified activity (addition/Reduction) i.e. Scope of the audit', 'change' => 'activity_change', 'details' => 'activity_details'],
            ['label' => 'Change of number of employees (addition/Reduction)', 'change' => 'employee_change', 'details' => 'employee_details']
        ];
        
        foreach ($changes as $change) {
            $hasChange = $submission[$change['change']] === 'Yes';
            ?>
            <div class="change-row">
                <div class="change-label">
                    <?php echo $change['label']; ?>: 
                    No change<span class="checkbox <?php echo !$hasChange ? 'checked' : ''; ?>"></span>, 
                    IF change?<span class="checkbox <?php echo $hasChange ? 'checked' : ''; ?>"></span>
                </div>
                <?php if ($hasChange && !empty($submission[$change['details']])): ?>
                    <div class="details">
                        <?php echo nl2br(htmlspecialchars($submission[$change['details']], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
        
        <!-- Consultancy -->
        <div style="margin-top: 15px;">
            <div class="change-label">
                Have consultancy services or in-house training been performed after certified by AITF on the subject of ISO 9001:
                <span class="checkbox <?php echo $submission['consultancy_services'] === 'Yes' ? 'checked' : ''; ?>"></span> Yes
                <span class="checkbox <?php echo $submission['consultancy_services'] === 'No' ? 'checked' : ''; ?>"></span> no
            </div>
            <?php if ($submission['consultancy_services'] === 'Yes' && !empty($submission['consultancy_details'])): ?>
                <div style="margin-top: 5px;">
                    <strong>If yes, by whom?:</strong>
                    <div class="details">
                        <?php echo nl2br(htmlspecialchars($submission['consultancy_details'], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Note -->
        <div class="note">
            <strong>Note –</strong>
            <ul>
                <li>Surveillance manday will be decided with considering present information of the organisation status.</li>
                <li>Any change in organisation production/Service realisation Process flow, organisation brochure, and customer specific requirements, please enclose with this form.</li>
            </ul>
        </div>
        
        <!-- Signature -->
        <div class="signature">
            <p>We herewith confirm the completeness and accuracy of the change of information given above.</p>
            
            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">
                        <strong>Name:</strong>
                        <?php echo htmlspecialchars($submission['sign_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td rowspan="3" style="width: 50%; text-align: center;">
                        <strong>Sign with duly stamped</strong>
                        <?php if (!empty($submission['signature_image'])): ?>
                            <?php 
                            $sig_path = htmlspecialchars($submission['signature_image'], ENT_QUOTES, 'UTF-8');
                            $ext = strtolower(pathinfo($sig_path, PATHINFO_EXTENSION));
                            if ($ext !== 'pdf'): ?>
                                <br><img src="<?php echo $sig_path; ?>" class="sig-img" alt="Signature">
                            <?php else: ?>
                                <br><em>[Signature PDF]</em>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Designation:</strong>
                        <?php echo htmlspecialchars($submission['sign_designation'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Date:</strong>
                        <?php echo htmlspecialchars($submission['sign_date'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <strong>FOR COMMUNICATION TO AITF- REGISTERED OFFICE ADDRESS:</strong>
            <p>
                <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong><br>
                Devi Indrayani Wing B 26, Gat No. 382+3+4/1, Talawade, Pune – 411 062. Maharashtra, India.
            </p>
            <p>
                <strong>OFFICE PHONE</strong> ☎ 020 65116677, <strong>WEB SITE</strong> – www.aitf.co.in
            </p>
            <p>
                <strong>Communication MAIL ID</strong> – aitfmcl@gmail.com (CERTIFICATION MANAGER)
            </p>
        </div>
        
        <div class="footer">
            <strong>FOR AITF REVIEW :</strong>
            <div class="review-box">
                <p>Sign of reviewer (AITF)</p>
            </div>
        </div>
        
        <!-- Meta Table at End -->
        <table class="meta" style="margin-top: 15px;">
            <tr>
                <td><strong>Form No. F 7.31</strong></td>
                <td><strong>Rev.:</strong>00, <strong>Date:</strong> 01.10.2013</td>
                <td><strong>Issue.:</strong> 01, <strong>Date:</strong> 01.10.2013</td>
                <td><strong>Page:</strong> 1 of 1</td>
            </tr>
        </table>
    </div>
</body>
</html>

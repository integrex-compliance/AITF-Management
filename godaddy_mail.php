<?php
// godaddy_mail.php
// GoDaddy Workspace Webmail Simulation

include 'dbConnect.php';

$sql = "SELECT 
            c.id AS customer_id,
            c.customer_name,
            c.email_id,
            cas.ca_id,
            cas.signed_notification AS stage1_doc,
            cas.signed_copy AS stage1_plan_doc,
            cas.signed_notification_II AS stage2_doc,
            s.service_id AS main_service_id,
            cas.audit_date AS stage1_date,
            cas.stage2_audit AS stage2_date,
            soi.audit_stage_id AS stage1_noti_id,
            asi.audit_schedule_id AS stage1_plan_id,
            stai.audit_stageii_id AS stage2_noti_id,
            asii.audit_scheduleii_id AS stage2_plan_id,
            (SELECT intimation_date FROM stage_oneaudit_id so WHERE so.ca_service_id = cas.ca_id ORDER BY so.audit_stage_id DESC LIMIT 1) AS stage1_sent_date,
            (SELECT intimation_date FROM stage_twoaudit_id st WHERE st.service_id = cas.ca_id ORDER BY st.audit_stageii_id DESC LIMIT 1) AS stage2_sent_date
        FROM service_type_ca cas
        INNER JOIN service s ON s.service_id = cas.service
        INNER JOIN customer_info c ON c.id = s.customer_id
        LEFT JOIN stage_oneaudit_id soi ON cas.ca_id = soi.ca_service_id
        LEFT JOIN audit_schedule_id asi ON cas.ca_id = asi.serviceca_id
        LEFT JOIN stage_twoaudit_id stai ON cas.ca_id = stai.service_id
        LEFT JOIN audit_scheduleii_id asii ON cas.ca_id = asii.ca_service
        WHERE (c.is_deleted IS NULL OR c.is_deleted <> 'YES')
        GROUP BY cas.ca_id
        ORDER BY c.customer_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sent_entries = array();

function formatGoDaddyDate($d)
{
    if (!$d)
        return '';
    $ts = strtotime($d);
    if ($ts === false)
        return (string) $d;
    // e.g. May 20
    return date('M j', $ts);
}

foreach ($rows as $r) {
    $customerName = isset($r['customer_name']) ? $r['customer_name'] : '';
    $emailId = isset($r['email_id']) ? $r['email_id'] : '';
    $stage1Sent = !empty($r['stage1_sent_date']) ? $r['stage1_sent_date'] : $r['stage1_date'];
    $stage2Sent = !empty($r['stage2_sent_date']) ? $r['stage2_sent_date'] : $r['stage2_date'];

    if ($stage1Sent) {
        $attachments = array();
        if ($r['stage1_noti_id']) {
            $attachments[] = array('name' => 'Release - Notification for Stage - I audit.pdf', 'url' => 'AuditNotificationandAssignment.php', 'type' => 'post', 'params' => array('auditid' => $r['stage1_noti_id'], 'serviceid' => $r['ca_id'], 'customerid' => $r['customer_id']));
        }
        if ($r['stage1_plan_id']) {
            $attachments[] = array('name' => 'Release - Stage - I Audit Plan.pdf', 'url' => 'AuditSchedule.php', 'type' => 'post', 'params' => array('auditscheduleid' => $r['stage1_plan_id'], 'serviceid' => $r['ca_id'], 'customerid' => $r['customer_id']));
        }
        if ($r['stage1_doc']) {
            $attachments[] = array('name' => 'Signed Notification Stage I.pdf', 'url' => $r['stage1_doc'], 'type' => 'static');
        }
        if ($r['stage1_plan_doc']) {
            $attachments[] = array('name' => 'Audit Plan Scanned Copy.pdf', 'url' => $r['stage1_plan_doc'], 'type' => 'static');
        }

        $sent_entries[] = array(
            'id' => 'sent_1_' . $r['customer_id'] . '_' . $r['ca_id'],
            'customer_name' => $customerName,
            'email_id' => $emailId,
            'subject' => "Notification of Assessment - Stage I - " . $customerName,
            'date_raw' => $stage1Sent,
            'date' => formatGoDaddyDate($stage1Sent),
            'stage' => 'Stage I',
            'attachments' => $attachments
        );
    }

    if ($stage2Sent) {
        $attachments = array();
        if ($r['stage2_noti_id']) {
            $attachments[] = array('name' => 'Release - Notification for Stage - II audit.pdf', 'url' => 'AuditNotificationStage2.php', 'type' => 'post', 'params' => array('notificationid' => $r['stage2_noti_id']));
        }
        if ($r['stage2_plan_id']) {
            $attachments[] = array('name' => 'Release - Stage - II Audit Plan.pdf', 'url' => 'SurveillanceAudit2.php', 'type' => 'post', 'params' => array('auditnotiid' => $r['stage2_plan_id'], 'serviceid' => $r['ca_id'], 'customerid' => $r['customer_id']));
        }
        if ($r['stage2_doc']) {
            $attachments[] = array('name' => 'Signed Notification Stage II.pdf', 'url' => $r['stage2_doc'], 'type' => 'static');
        }

        $sent_entries[] = array(
            'id' => 'sent_2_' . $r['customer_id'] . '_' . $r['ca_id'],
            'customer_name' => $customerName,
            'email_id' => $emailId,
            'subject' => "Notification of Assessment - Stage II - " . $customerName,
            'date_raw' => $stage2Sent,
            'date' => formatGoDaddyDate($stage2Sent),
            'stage' => 'Stage II',
            'attachments' => $attachments
        );
    }
}

usort($sent_entries, function ($a, $b) {
    $aTs = $a['date_raw'] ? strtotime($a['date_raw']) : 0;
    $bTs = $b['date_raw'] ? strtotime($b['date_raw']) : 0;
    if ($aTs === $bTs) {
        return strcasecmp($a['customer_name'], $b['customer_name']);
    }
    return $bTs <=> $aTs; // Descending
});

$sent_json = json_encode($sent_entries);

$inbox_emails = [
    [
        'id' => 'inbox_1',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - JAL AUTOMATION AND SYSTEMS',
        'date' => 'Jun 8',
        'snippet' => 'Customer: JAL AUTOMATION AND SYSTEMS, Subject: Reminder for Withdrawal of ISO 9001 Certificate...',
        'body' => 'Client letter sent successfully.<br><br>
        <strong>Customer:</strong> JAL AUTOMATION AND SYSTEMS<br>
        <strong>Recipients:</strong> sdj@jalautomation.com<br>
        <strong>Subject:</strong> Reminder for Withdrawal of ISO 9001 Certificate - JAL AUTOMATION AND SYSTEMS<br><br>
        <strong>Message sent to client:</strong><br><br>
        Dear JAL AUTOMATION AND SYSTEMS,<br><br>
        We would like to inform you that your ISO certification is currently under withdrawal review due to pending certification requirements.<br><br>
        Please find the attached Withdrawal Reminder for complete details, including the reason and the actions needed.<br><br>
        Kindly review the letter and share your corrective action plan with supporting documents within the mentioned timeline. Once we receive and verify the required information, we will move ahead accordingly.<br><br>
        If you have already completed the necessary actions, please ignore this email and send us the relevant documents for our review.<br><br>
        We look forward to your support in resolving this at the earliest.<br><br>
        Sincerely,<br>
        AITF Certification<br><br>
        <strong>Attachment:</strong> Withdrawal Reminder.pdf'
    ],
    [
        'id' => 'inbox_2',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - OM HEAT TREAT INDUSTRIES',
        'date' => 'Jun 8',
        'snippet' => 'New Audit Application Received. This online audit application was submitted via the AITF website...',
        'body' => '<h3>New Audit Application Received</h3>
        <p>This online audit application was submitted via the AITF website. The completed form PDF is attached below for your verification.</p>
        <table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
            <tr><td><strong>Organization name</strong></td><td>OM HEAT TREAT INDUSTRIES</td></tr>
            <tr><td><strong>Contact person</strong></td><td>Mr. Shreemant Bandichode (Plant Head)</td></tr>
            <tr><td><strong>Telephone / Mobile</strong></td><td>9850889145</td></tr>
            <tr><td><strong>E-Mail Id</strong></td><td>omheattreatment@yaghoo.com</td></tr>
            <tr><td><strong>Address</strong></td><td>Plot No: - BG 106/2, T- Block MIDC, Bhosari, Pune 411026</td></tr>
            <tr><td><strong>Standards applied for</strong></td><td>ISO 9001 (NABCB)</td></tr>
            <tr><td><strong>GSTIN Number</strong></td><td>27ASBPB7867P2Z3</td></tr>
        </table>'
    ],
    [
        'id' => 'inbox_3',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - JADHAV ENGINEERING WORKS',
        'date' => 'Jun 7',
        'snippet' => 'Customer: JADHAV ENGINEERING WORKS, Subject: Reminder for Withdrawal of ISO 9001 Certificate...',
        'body' => 'Client letter sent successfully.<br><br>
        <strong>Customer:</strong> JADHAV ENGINEERING WORKS<br>
        <strong>Recipients:</strong> jadhavengineering@gmail.com<br>
        <strong>Subject:</strong> Reminder for Withdrawal of ISO 9001 Certificate - JADHAV ENGINEERING WORKS<br><br>
        <strong>Message sent to client:</strong><br><br>
        Dear JADHAV ENGINEERING WORKS,<br><br>
        We would like to inform you that your ISO certification is currently under withdrawal review due to pending certification requirements.<br><br>
        Please find the attached Withdrawal Reminder for complete details, including the reason and the actions needed.<br><br>
        Sincerely,<br>
        AITF Certification'
    ],
    [
        'id' => 'inbox_4',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Alca Technologies',
        'date' => 'Jun 7',
        'snippet' => 'New Audit Application Received from Alca Technologies...',
        'body' => '<h3>New Audit Application Received</h3>
        <p>This online audit application was submitted via the AITF website.</p>
        <table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
            <tr><td><strong>Organization name</strong></td><td>Alca Technologies</td></tr>
            <tr><td><strong>Contact person</strong></td><td>Gaurav Oli</td></tr>
            <tr><td><strong>Telephone / Mobile</strong></td><td>8424859833</td></tr>
            <tr><td><strong>E-Mail Id</strong></td><td>gauravoli777@gmail.com</td></tr>
            <tr><td><strong>Address</strong></td><td>W-16, S-Block, MIDC Area, Bhosari, Pune-411 026</td></tr>
        </table>'
    ],
    [
        'id' => 'inbox_5',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Pla Craft Engineering',
        'date' => 'Jun 6',
        'snippet' => 'Customer: Pla Craft Engineering, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Pla Craft Engineering<br><strong>Recipients:</strong> placraft0128@gmail.com<br><strong>Subject:</strong> Reminder for Withdrawal'
    ],
    [
        'id' => 'inbox_6',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Sharv Polyplast Pvt. Ltd.',
        'date' => 'Jun 6',
        'snippet' => 'New Audit Application Received from Sharv Polyplast Pvt. Ltd....',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Sharv Polyplast Pvt. Ltd.</td></tr><tr><td><strong>Contact person</strong></td><td>Mr.Pranav Kalbande</td></tr><tr><td><strong>E-Mail Id</strong></td><td>pranavkalbande@sharvppl.com</td></tr></table>'
    ],
    [
        'id' => 'inbox_7',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Heramb Enterprises',
        'date' => 'Jun 5',
        'snippet' => 'Customer: Heramb Enterprises, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Heramb Enterprises<br><strong>Recipients:</strong> herambpune@gmail.com'
    ],
    [
        'id' => 'inbox_8',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Pethkar Industries',
        'date' => 'Jun 5',
        'snippet' => 'New Audit Application Received from Pethkar Industries...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Pethkar Industries</td></tr><tr><td><strong>Contact person</strong></td><td>Suryakant Pethkar</td></tr></table>'
    ],
    [
        'id' => 'inbox_9',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Vigneshwara Fabricators',
        'date' => 'Jun 4',
        'snippet' => 'Customer: Vigneshwara Fabricators, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Vigneshwara Fabricators<br><strong>Recipients:</strong> Vigneshwarafabricators@yahoo.co.in'
    ],
    [
        'id' => 'inbox_10',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Sagar Engineering',
        'date' => 'Jun 4',
        'snippet' => 'New Audit Application Received from Sagar Engineering...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Sagar Engineering</td></tr><tr><td><strong>Contact person</strong></td><td>Mr. Ishwar Phale</td></tr></table>'
    ],
    [
        'id' => 'inbox_11',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Suyog Engineering',
        'date' => 'Jun 3',
        'snippet' => 'Customer: Suyog Engineering, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Suyog Engineering<br><strong>Recipients:</strong> suyogpune@yahoo.co.in'
    ],
    [
        'id' => 'inbox_12',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Phoenix Engineers',
        'date' => 'Jun 3',
        'snippet' => 'New Audit Application Received from Phoenix Engineers...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Phoenix Engineers</td></tr><tr><td><strong>Contact person</strong></td><td>Mr.Shahaji Chavan</td></tr></table>'
    ],
    [
        'id' => 'inbox_13',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Bhaldar Industries',
        'date' => 'Jun 2',
        'snippet' => 'Customer: Bhaldar Industries, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Bhaldar Industries<br><strong>Recipients:</strong> bhaldarindustries@gmail.com'
    ],
    [
        'id' => 'inbox_14',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - C. K. Engineering',
        'date' => 'Jun 2',
        'snippet' => 'New Audit Application Received from C. K. Engineering...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>C. K. Engineering</td></tr><tr><td><strong>Contact person</strong></td><td>Mr.Mohan Naik</td></tr></table>'
    ],
    [
        'id' => 'inbox_15',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Laxmi Engineering Works',
        'date' => 'Jun 1',
        'snippet' => 'Customer: Laxmi Engineering Works, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Laxmi Engineering Works<br><strong>Recipients:</strong> laxmiengineering55@yahoo.in'
    ],
    [
        'id' => 'inbox_16',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - SGM Precision',
        'date' => 'Jun 1',
        'snippet' => 'Customer: SGM Precision, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> SGM Precision<br><strong>Recipients:</strong> japtech_srj@yahoo.co.in'
    ],
    [
        'id' => 'inbox_17',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Global Automation',
        'date' => 'May 31',
        'snippet' => 'Customer: Global Automation, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Global Automation<br><strong>Recipients:</strong> global_0809@rediffmail.com'
    ],
    [
        'id' => 'inbox_18',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Shree Polymers',
        'date' => 'May 31',
        'snippet' => 'New Audit Application Received from Shree Polymers...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Shree Polymers</td></tr><tr><td><strong>Contact person</strong></td><td>Mr.Rupesh Garg</td></tr></table>'
    ],
    [
        'id' => 'inbox_19',
        'sender' => 'AITF Certification',
        'subject' => 'Client letter sent successfully. - Aaron Enterprises',
        'date' => 'May 30',
        'snippet' => 'Customer: Aaron Enterprises, Subject: Reminder for Withdrawal...',
        'body' => 'Client letter sent successfully.<br><br><strong>Customer:</strong> Aaron Enterprises<br><strong>Recipients:</strong> sindhugnair399473@gmail.com'
    ],
    [
        'id' => 'inbox_20',
        'sender' => 'AITF Certification',
        'subject' => 'New Audit Application Received - Nilkanth Industries',
        'date' => 'May 30',
        'snippet' => 'New Audit Application Received from Nilkanth Industries...',
        'body' => '<h3>New Audit Application Received</h3><table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;"><tr><td><strong>Organization name</strong></td><td>Nilkanth Industries</td></tr><tr><td><strong>Contact person</strong></td><td>Mr.sharad kale</td></tr></table>'
    ]
];
$om_heat_id = 0;
try {
    $stmt_om = $db->prepare("SELECT id FROM online_audit_applications WHERE org_name LIKE '%OM HEAT TREAT%' ORDER BY id DESC LIMIT 1");
    $stmt_om->execute();
    $om_row = $stmt_om->fetch(PDO::FETCH_ASSOC);
    if ($om_row) {
        $om_heat_id = (int) $om_row['id'];
    } else {
        $stmt_latest = $db->query("SELECT id FROM online_audit_applications ORDER BY id DESC LIMIT 1");
        $latest_row = $stmt_latest->fetch(PDO::FETCH_ASSOC);
        if ($latest_row) {
            $om_heat_id = (int) $latest_row['id'];
        }
    }
} catch (Exception $e) {
    $om_heat_id = 0;
}
$inbox_json = json_encode($inbox_emails);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoDaddy Webmail</title>
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233b82f6'%3E%3Cpath d='M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z'/%3E%3C/svg%3E">
    <!-- Use FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #333;
            overflow: hidden;
        }

        .app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }



        .sidebar {
            width: 260px;
            background-color: #1b2031;
            color: #aebcd2;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .brand-header {
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: white;
            font-weight: 600;
            font-size: 18px;
            gap: 12px;
            cursor: pointer;
        }

        .brand-header i.fa-envelope-square {
            color: #2b70f0;
            font-size: 24px;
        }

        .account-selector {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: background 0.2s;
        }

        .account-selector:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #8b5cf6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .account-email {
            font-size: 14px;
            color: white;
            font-weight: 500;
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-menu {
            list-style: none;
            padding: 15px 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-item.active {
            background-color: #272e48;
            color: white;
            border-left: 3px solid #3b82f6;
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .setup-widget {
            margin: 20px;
            background-color: #272e48;
            border-radius: 8px;
            padding: 15px;
            position: relative;
        }

        .setup-widget h4 {
            margin: 0 0 10px 0;
            color: white;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .progress-bar {
            height: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 37%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            border-radius: 2px;
        }

        .progress-text {
            font-size: 11px;
            margin-top: 6px;
            color: #8b99af;
        }

        /* Middle Pane (List) */
        .mail-list-pane {
            width: 380px;
            border-right: 1px solid #eaedf3;
            display: flex;
            flex-direction: column;
            background-color: white;
            flex-shrink: 0;
        }

        .list-header {
            height: 60px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eaedf3;
            justify-content: space-between;
        }

        .new-email-btn {
            background-color: #2b70f0;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 38px;
        }

        .list-controls {
            padding: 12px 20px;
            border-bottom: 1px solid #eaedf3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .mail-items {
            overflow-y: auto;
            flex-grow: 1;
        }

        .mail-item {
            padding: 15px 20px 15px 45px;
            border-bottom: 1px solid #eaedf3;
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
        }

        .mail-item:hover {
            background-color: #f8fafc;
        }

        .mail-item.selected {
            background-color: #eff6ff;
        }

        .mail-checkbox {
            position: absolute;
            left: 15px;
            top: 18px;
            width: 16px;
            height: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background-color: white;
        }

        .mail-sender {
            font-weight: 600;
            font-size: 14px;
            color: #111;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        .mail-date {
            font-weight: 400;
            font-size: 12px;
            color: #64748b;
        }

        .mail-subject {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mail-snippet {
            font-size: 13px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Right Pane (Preview) */
        .preview-pane {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #f4f6f8;
            /* Titan grey-blue background */
            overflow: hidden;
        }

        .top-navbar {
            height: 60px;
            border-bottom: 1px solid #eaedf3;
            display: flex;
            align-items: center;
            padding: 0 24px;
            justify-content: space-between;
            background-color: #ffffff;
            /* Explicit white background */
            box-sizing: border-box;
        }

        .search-bar {
            position: relative;
            width: 500px;
            display: flex;
            align-items: center;
        }

        .search-bar input {
            width: 100%;
            height: 38px;
            padding: 0 35px 0 35px;
            background-color: #f1f3f5;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            color: #1e293b;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .search-bar input:focus {
            border-color: #3b82f6;
            background-color: white;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }

        .search-bar .fa-search {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 13px;
            pointer-events: none;
        }

        .search-bar .fa-angle-down {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 14px;
            cursor: pointer;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #64748b;
        }

        .nav-actions i {
            font-size: 18px;
            cursor: pointer;
        }

        .nav-actions i:hover {
            color: #111;
        }

        .upgrade-btn {
            background-color: #f0a841;
            /* GoDaddy warm gold */
            color: #000000;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 38px;
            box-sizing: border-box;
            transition: background-color 0.2s;
        }

        .upgrade-btn:hover {
            background-color: #ebb354;
        }

        .preview-toolbar {
            height: 50px;
            border-bottom: 1px solid #eaedf3;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 24px;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
            background-color: #ffffff;
            /* Explicit white background */
            box-sizing: border-box;
        }

        .toolbar-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .toolbar-btn:hover {
            color: #111;
        }

        .preview-content {
            padding: 24px;
            overflow-y: auto;
            flex-grow: 1;
            display: none;
            box-sizing: border-box;
        }

        .empty-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #64748b;
            font-size: 15px;
        }

        .empty-preview img {
            width: 250px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        /* Email Card Layout */
        .email-card {
            background-color: #ffffff;
            border: 1px solid #eaedf3;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 28px;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .email-subject {
            font-size: 20px;
            font-weight: 600;
            color: #111111;
        }

        .email-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .sender-info {
            display: flex;
            gap: 12px;
        }

        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
        }

        .sender-details .name {
            font-weight: 600;
            font-size: 14px;
            color: #111;
        }

        .sender-details .email-address {
            font-size: 13px;
            color: #64748b;
        }

        .sender-details .email {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .email-date-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #64748b;
            font-size: 13px;
        }

        .email-body {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            padding-left: 52px;
        }

        /* Attachment Styles */
        .attachments-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .attachment-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 280px;
            background-color: #f8fafc;
            cursor: pointer;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .attachment-card:hover {
            border-color: #cbd5e1;
            background-color: #f1f5f9;
        }

        .attachment-icon {
            width: 36px;
            height: 36px;
            background-color: #ef4444;
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .attachment-info {
            overflow: hidden;
        }

        .attachment-name {
            font-weight: 500;
            font-size: 12px;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attachment-size {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .ai-summary {
            border: 1px solid #dcdfe6;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #3b82f6;
            display: flex;
            align-items: center;
            gap: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .ai-summary:hover {
            border-color: #3b82f6;
            background-color: #f0f7ff;
        }

        /* Action Footer Box */
        .email-footer-actions {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eaedf3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-action-btn {
            background-color: transparent;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            outline: none;
        }

        .footer-action-btn:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .ai-reply-btn {
            background-color: transparent;
            border: 1px solid #e9d5ff;
            border-radius: 20px;
            color: #7c3aed;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            outline: none;
        }

        .ai-reply-btn:hover {
            background-color: #f3e8ff;
            border-color: #d8b4fe;
        }

        .ai-spark {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: #7c3aed;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
        }

        /* Sidebar Split Button Custom Styles */
        .new-email-main-btn,
        .new-email-dropdown-btn {
            transition: background-color 0.2s;
        }
    </style>
</head>

<body>

    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand-header" onclick="window.location.href='homePage.php'">
                <i class="fa fa-envelope-square"></i>
                GoDaddy
            </div>

            <div class="account-selector">
                <div class="avatar" style="background-color: #8b5cf6;">C</div>
                <div class="account-email">certification@aitf.co.in</div>
                <span style="font-size: 13px; color: #3b82f6; font-weight: 600; margin-right: 8px;">28</span>
                <i class="fa fa-angle-down"></i>
            </div>

            <ul class="nav-menu">
                <li class="nav-item active" data-folder="inbox"
                    style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 15px;">
                        <i class="fa fa-inbox"></i> Inbox
                    </span>
                    <span style="font-size: 12px; color: #3b82f6; font-weight: 600; margin-right: 5px;">28</span>
                </li>
                <li class="nav-item" data-folder="starred">
                    <i class="fa fa-star"></i> Starred
                </li>
                <li class="nav-item" data-folder="sent">
                    <i class="fa fa-paper-plane"></i> Sent
                </li>
                <li class="nav-item" data-folder="less">
                    <i class="fa fa-angle-up"></i> Less
                </li>
                <li class="nav-item" data-folder="spam">
                    <i class="fa fa-exclamation-circle"></i> Spam
                </li>
            </ul>

            <div class="setup-widget">
                <i class="fa fa-times"
                    style="position: absolute; right: 10px; top: 10px; font-size: 12px; cursor: pointer;"></i>
                <h4>👋 Finish your setup</h4>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">37% done</div>
            </div>

            <!-- Bottom action buttons as seen in screenshot -->
            <div
                style="display: flex; padding: 10px; border-top: 1px solid rgba(255,255,255,0.05); gap: 10px; font-size: 12px; justify-content: center;">
                <div style="cursor: pointer;"><i class="fa fa-bug"></i> Bug</div>
                <div style="cursor: pointer;"><i class="fa fa-lightbulb-o"></i> Feature</div>
            </div>
        </div>

        <!-- Middle Pane (Mail List) -->
        <div class="mail-list-pane">
            <div class="list-header"
                style="height: 60px; padding: 0 20px; display: flex; align-items: center; border-bottom: 1px solid #eaedf3; background-color: white; box-sizing: border-box;">
                <div class="new-email-split-btn"
                    style="display: flex; height: 38px; border-radius: 6px; overflow: hidden; background-color: #2b70f0;">
                    <button class="new-email-main-btn"
                        style="background-color: #2b70f0; color: white; border: none; padding: 0 16px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; border-right: 1px solid rgba(255,255,255,0.15); height: 100%;">
                        <i class="fa fa-pencil-square-o"></i> New email
                    </button>
                    <button class="new-email-dropdown-btn"
                        style="background-color: #2b70f0; color: white; border: none; padding: 0 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 100%;">
                        <i class="fa fa-angle-down"></i>
                    </button>
                </div>
            </div>

            <div class="list-controls">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div class="mail-checkbox" style="position:relative; left:0; top:0;"></div>
                    <span id="folder-title">Inbox</span>
                </div>
                <div style="color: #64748b; font-weight: 400; font-size: 13px; cursor:pointer;">
                    All emails <i class="fa fa-angle-down"></i>
                </div>
            </div>

            <div class="mail-items" id="mail-list-container">
                <!-- Mail items will be injected here via JS -->
            </div>
        </div>

        <!-- Right Pane (Preview) -->
        <div class="preview-pane">
            <div class="top-navbar">
                <div class="search-bar">
                    <i class="fa fa-search"></i>
                    <input type="text" placeholder="Search in certification@aitf.co.in">
                    <i class="fa fa-angle-down"></i>
                </div>
                <div class="nav-actions">
                    <i class="fa fa-eye" title="View options"></i>
                    <i class="fa fa-th" title="App launcher"></i>
                    <i class="fa fa-cog" title="Settings"></i>
                    <i class="fa fa-question-circle-o" title="Help"></i>
                    <button class="upgrade-btn">
                        <i class="fa fa-arrow-circle-up" style="color: #000000; font-size: 14px;"></i> Upgrade
                    </button>
                    <div style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <div style="text-align: right; line-height: 1.1; font-family: sans-serif;">
                            <div style="font-size: 8px; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px;">
                                POWERED BY</div>
                            <div style="font-size: 13px; color: #64748b; font-weight: 700; letter-spacing: 1px;">TITAN
                            </div>
                        </div>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 4h16v3H4zm6 3h4v13h-4z" fill="#64748b" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="preview-toolbar">
                <div class="toolbar-btn"><i class="fa fa-archive"></i> Archive</div>
                <div class="toolbar-btn"><i class="fa fa-trash-o"></i> Delete</div>
                <div class="toolbar-btn"><i class="fa fa-star-o"></i> Star</div>
                <div class="toolbar-btn"><i class="fa fa-envelope-o"></i> Mark unread</div>
                <div class="toolbar-btn"><i class="fa fa-ban"></i> Block</div>
                <div class="toolbar-btn"><i class="fa fa-ellipsis-h"></i></div>
            </div>

            <!-- Empty State -->
            <div class="empty-preview" id="empty-preview">
                <svg width="240" height="180" viewBox="0 0 240 180" fill="none" xmlns="http://www.w3.org/2000/svg"
                    style="margin-bottom: 20px;">
                    <rect x="30" y="40" width="180" height="110" rx="6" fill="#e2e8f0" />
                    <rect x="40" y="55" width="60" height="8" rx="4" fill="#cbd5e1" />
                    <rect x="40" y="75" width="140" height="6" rx="3" fill="#cbd5e1" />
                    <rect x="40" y="90" width="120" height="6" rx="3" fill="#cbd5e1" />
                    <rect x="40" y="105" width="150" height="6" rx="3" fill="#cbd5e1" />
                    <rect x="40" y="120" width="90" height="6" rx="3" fill="#cbd5e1" />
                    <circle cx="150" cy="110" r="40" fill="#3b82f6" opacity="0.2" />
                    <path d="M140 110L148 118L165 100" stroke="#3b82f6" stroke-width="4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                Select a mail to see preview
            </div>

            <!-- Content State -->
            <div class="preview-content" id="preview-content">
                <!-- Subject Header (Outside Card) -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div class="email-subject" id="preview-subject">Subject</div>
                    <div class="ai-summary" id="preview-ai-summary"><i class="fa fa-magic"></i> AI Summary</div>
                </div>

                <!-- Email Content Card -->
                <div class="email-card">
                    <!-- Metadata Header -->
                    <div class="email-meta">
                        <div class="sender-info">
                            <div class="sender-avatar" id="preview-avatar">S</div>
                            <div class="sender-details">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span class="name" id="preview-sender">Sender</span>
                                    <span class="email-address"
                                        id="preview-email-address">&lt;certification@aitf.co.in&gt;</span>
                                </div>
                                <div class="email" id="preview-email">To: me</div>
                            </div>
                        </div>
                        <div class="email-date-actions">
                            <span id="preview-date">Date</span>
                            <span
                                style="display: flex; align-items: center; gap: 4px; color: #94a3b8; font-size: 12px; margin-right: 5px;">
                                <i class="fa fa-paperclip"></i> <span id="attachment-count-indicator">1</span> <i
                                    class="fa fa-angle-down"></i>
                            </span>
                            <i class="fa fa-reply" style="cursor:pointer;" title="Reply"></i>
                            <i class="fa fa-ellipsis-h" style="cursor:pointer;" title="More options"></i>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="email-body" id="preview-body">
                        <!-- Body injected here -->
                    </div>

                    <!-- Attachments Section (inside the card) -->
                    <div id="attachments-section" style="margin-top: 30px; display: none; padding-left: 52px;">
                        <div style="height: 1px; border-top: 1px dashed #e2e8f0; margin-bottom: 15px;"></div>
                        <div
                            style="font-size: 12px; color: #10b981; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                            <i class="fa fa-check-circle"></i> Attachment scanned, safe to open
                        </div>
                        <div class="attachments-container" id="preview-attachments">
                            <!-- Attachments injected here -->
                        </div>
                    </div>

                    <!-- Action Footer Box -->
                    <div class="email-footer-actions"
                        style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eaedf3; display: flex; justify-content: space-between; align-items: center; padding-left: 52px;">
                        <div style="display: flex; gap: 12px;">
                            <button class="footer-action-btn" onclick="alert('Reply action clicked')"><i
                                    class="fa fa-reply"></i> Reply</button>
                            <button class="footer-action-btn" onclick="alert('Reply all action clicked')"><i
                                    class="fa fa-reply-all"></i> Reply all</button>
                            <button class="footer-action-btn" onclick="alert('Forward action clicked')"><i
                                    class="fa fa-share"></i> Forward</button>
                        </div>
                        <div>
                            <button class="ai-reply-btn"><span class="ai-spark"><i class="fa fa-bolt"></i></span> Reply
                                with AI</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const inboxData = <?php echo $inbox_json; ?>;
        const sentData = <?php echo $sent_json; ?>;

        let currentFolder = 'inbox';
        let currentMails = inboxData;

        const mailListContainer = document.getElementById('mail-list-container');
        const folderTitle = document.getElementById('folder-title');
        const emptyPreview = document.getElementById('empty-preview');
        const previewContent = document.getElementById('preview-content');

        // Elements for preview rendering
        const preSubject = document.getElementById('preview-subject');
        const preAvatar = document.getElementById('preview-avatar');
        const preSender = document.getElementById('preview-sender');
        const preEmailAddress = document.getElementById('preview-email-address');
        const preEmail = document.getElementById('preview-email');
        const preDate = document.getElementById('preview-date');
        const preBody = document.getElementById('preview-body');
        const preAttachments = document.getElementById('preview-attachments');
        const attachmentsSection = document.getElementById('attachments-section');
        const attachmentCountIndicator = document.getElementById('attachment-count-indicator');

        function renderList() {
            mailListContainer.innerHTML = '';

            if (currentMails.length === 0) {
                mailListContainer.innerHTML = '<div style="padding: 30px; text-align: center; color: #94a3b8; font-size: 14px;">No emails found.</div>';
                return;
            }

            currentMails.forEach(mail => {
                // Sent tab mapping: we mocked properties differently for sent tab
                const isSent = currentFolder === 'sent';
                const senderName = isSent ? `me, ${mail.customer_name}` : (mail.sender === 'AITF Certification' ? 'me' : mail.sender);
                const subjectText = mail.subject;
                const snippetText = isSent ? `Dear Sir, Please find attached documents for...` : mail.snippet;
                const dateText = mail.date;
                const hasAttachment = isSent || mail.id === 'inbox_1' || mail.id === 'inbox_2';

                const mailDiv = document.createElement('div');
                mailDiv.className = 'mail-item';
                mailDiv.onclick = () => selectMail(mail, mailDiv);

                mailDiv.innerHTML = `
                    <div class="mail-checkbox"></div>
                    <div class="mail-sender">
                        <span>${senderName}</span>
                        <div style="display:flex; align-items:center; gap:8px;">
                            ${hasAttachment ? '<i class="fa fa-paperclip" style="color:#94a3b8; font-size:12px;"></i>' : ''}
                            <span class="mail-date">${dateText}</span>
                        </div>
                    </div>
                    <div class="mail-subject">${subjectText}</div>
                    <div class="mail-snippet">${snippetText}</div>
                `;

                mailListContainer.appendChild(mailDiv);
            });
        }

        function downloadDynamicPDF(url, params) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.target = '_blank';

            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = params[key];
                    form.appendChild(input);
                }
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function selectMail(mail, element) {
            // Update selection visual
            document.querySelectorAll('.mail-item').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');

            // Switch views
            emptyPreview.style.display = 'none';
            previewContent.style.display = 'block';

            const isSent = currentFolder === 'sent';

            if (isSent) {
                preSubject.textContent = mail.subject;
                preAvatar.textContent = "M"; // "Me"
                preAvatar.style.backgroundColor = "#10b981"; // Green for sent
                preSender.textContent = "me";
                preEmailAddress.textContent = "<certification@aitf.co.in>";
                preEmail.textContent = `To: ${mail.customer_name} <${mail.email_id || 'client@example.com'}>`;
                preDate.textContent = mail.date;

                preBody.innerHTML = `
                    Dear Sir/Madam,<br><br>
                    Greetings from <strong>AITF Management Certification Limited</strong>.<br><br>
                    With reference to your application for ISO Certification, we are pleased to inform you that your <strong>${mail.stage}</strong> evaluation has been scheduled. Attached to this email, please find the formal Notification and the detailed Audit Plan for your review.<br><br>
                    <strong>Attached Documents:</strong><br>
                    1. Release - Notification for ${mail.stage} audit<br>
                    2. Release - ${mail.stage} Audit Plan<br>
                    3. Scanned Copy of Signed Notification (if available)<br>
                    4. Scanned Copy of Audit Plan (if available)<br><br>
                    Please ensure that all relevant personnel and documentation are available during the audit dates. Our lead auditor will coordinate with you for any further requirements.<br><br>
                    If you have any queries or require any clarification regarding the audit schedule or process, please feel free to reach out to us.<br><br>
                    We look forward to a successful assessment.<br><br>
                    Best Regards,<br><br>
                    <strong>Certification Department</strong><br>
                    AITF Management Certification Limited<br>
                    <small>Email: certification@aitf.co.in | Web: www.aitf.co.in</small>
                `;

                // Show attachments
                if (mail.attachments && mail.attachments.length > 0) {
                    attachmentsSection.style.display = 'block';
                    preAttachments.style.display = 'flex';
                    preAttachments.innerHTML = '';
                    attachmentCountIndicator.textContent = mail.attachments.length;

                    mail.attachments.forEach((att, index) => {
                        const card = document.createElement('div');
                        card.className = 'attachment-card';

                        if (att.type === 'static') {
                            card.onclick = () => window.open(att.url, '_blank');
                        } else {
                            card.onclick = () => downloadDynamicPDF(att.url, att.params);
                        }

                        card.innerHTML = `
                            <div class="attachment-icon">
                                <i class="fa fa-file-pdf-o"></i>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">${att.name}</div>
                                <div class="attachment-size">PDF Document</div>
                            </div>
                        `;
                        preAttachments.appendChild(card);
                    });
                } else {
                    attachmentsSection.style.display = 'none';
                    attachmentCountIndicator.textContent = '0';
                }
            } else {
                preSubject.textContent = mail.subject;
                preAvatar.textContent = mail.sender === 'AITF Certification' ? 'M' : mail.sender.charAt(0).toUpperCase();
                preAvatar.style.backgroundColor = mail.sender === 'AITF Certification' ? '#8b5cf6' : '#3b82f6';
                preSender.textContent = mail.sender === 'AITF Certification' ? 'me' : mail.sender;
                preEmailAddress.textContent = mail.sender === 'AITF Certification' ? '<certification@aitf.co.in>' : `<${mail.sender.toLowerCase().replace(/\s+/g, '')}@aitf.co.in>`;
                preEmail.textContent = `To: me`;
                preDate.textContent = mail.date;
                preBody.innerHTML = mail.body;

                // Add mock attachments for inbox if applicable
                if (mail.id === 'inbox_1') {
                    attachmentsSection.style.display = 'block';
                    preAttachments.style.display = 'flex';
                    attachmentCountIndicator.textContent = '1';
                    preAttachments.innerHTML = `
                        <div class="attachment-card" onclick="alert('Opening Withdrawal Reminder.pdf...')">
                            <div class="attachment-icon">
                                <i class="fa fa-file-pdf-o"></i>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">Withdrawal Reminder.pdf</div>
                                <div class="attachment-size">15 KB</div>
                            </div>
                        </div>
                    `;
                } else if (mail.id === 'inbox_2') {
                    attachmentsSection.style.display = 'block';
                    preAttachments.style.display = 'flex';
                    attachmentCountIndicator.textContent = '1';
                    preAttachments.innerHTML = `
                        <div class="attachment-card" onclick="alert('Opening Audit Application Form.pdf...')">
                            <div class="attachment-icon">
                                <i class="fa fa-file-pdf-o"></i>
                            </div>
                            <div class="attachment-info">
                                <div class="attachment-name">Audit Application Form.pdf</div>
                                <div class="attachment-size">32 KB</div>
                            </div>
                        </div>
                    `;
                } else {
                    attachmentsSection.style.display = 'none';
                    attachmentCountIndicator.textContent = '0';
                }
            }
        }

        // Setup folder navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function () {
                // Update active state
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');

                // Change folder
                const folder = this.getAttribute('data-folder');
                currentFolder = folder;
                folderTitle.textContent = folder.charAt(0).toUpperCase() + folder.slice(1);

                if (folder === 'inbox') {
                    currentMails = inboxData;
                } else if (folder === 'sent') {
                    currentMails = sentData;
                } else {
                    currentMails = []; // Empty for Starred, Spam, etc.
                }

                // Reset preview
                emptyPreview.style.display = 'flex';
                previewContent.style.display = 'none';

                renderList();
            });
        });

        // Initial render
        renderList();
    </script>
</body>

</html>
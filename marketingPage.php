<div id="wrapper" class="active">
<?php
$pageIdentifier = "Mktg";
include 'header.php';
include 'dbConnect.php';
date_default_timezone_set('Asia/Kolkata');

function marketingText($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function marketingNormalizeStatus($value)
{
    $status = trim((string) $value);
    if ($status === '') {
        return 'No Status';
    }

    $key = strtolower(str_replace(array('-', '_', ' '), '', $status));
    if ($key === 'active' || $key === 'valid' || $key === 'granted' || $key === 'certified') {
        return 'Active';
    }
    if ($key === 'suspended' || $key === 'suspend') {
        return 'Suspended';
    }
    if ($key === 'expired' || $key === 'expire') {
        return 'Expired';
    }
    if ($key === 'withdrawn' || $key === 'withdraw') {
        return 'Withdrawn';
    }
    if ($key === 'applicationcanceled' || $key === 'applicationcancelled' || $key === 'cancelled' || $key === 'canceled') {
        return 'Application canceled';
    }
    if ($key === 'inprocess') {
        return 'In-process';
    }
    if ($key === 'contractinprocess') {
        return 'Contract In-process';
    }

    return $status;
}

function marketingDate($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        return '-';
    }

    $datePart = substr($value, 0, 10);
    return function_exists('simpleDateConversion') ? simpleDateConversion($datePart) : date('d.m.Y', strtotime($datePart));
}

function marketingColumnMap($db, $table)
{
    $columns = array();
    if (!$db) {
        return $columns;
    }

    try {
        $stmt = $db->query("SHOW COLUMNS FROM `" . $table . "`");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[$row['Field']] = true;
        }
    } catch (Exception $ex) {
        error_log($ex->getMessage());
    }

    return $columns;
}

function marketingFieldExpr($columns, $alias, $column, $as)
{
    if (isset($columns[$column])) {
        return $alias . ".`" . $column . "` AS `" . $as . "`";
    }
    return "NULL AS `" . $as . "`";
}

$statusSections = array(
    'New Client' => array(
        'tab_id' => 'tab-new',
        'title' => 'New Clients',
        'tone' => 'new',
        'icon' => 'fa-user-plus',
        'accent' => '#2563eb',
        'badge' => 'ar-badge-audit',
        'desc' => 'Fresh client enquiries waiting for first contact.'
    ),
    'Current Marketing' => array(
        'tab_id' => 'tab-mktg',
        'title' => 'Current Marketing',
        'tone' => 'marketing',
        'icon' => 'fa-bullhorn',
        'accent' => '#0891b2',
        'badge' => 'ar-badge-nc',
        'desc' => 'Active marketing pipeline in follow-up stage.'
    ),
    'Suspended' => array(
        'tab_id' => 'tab-suspended',
        'title' => 'Suspended',
        'tone' => 'suspended',
        'icon' => 'fa-pause-circle',
        'accent' => '#d97706',
        'badge' => 'ar-badge-auditor',
        'desc' => 'Suspended clients requiring immediate recovery action.'
    ),
    'Expired' => array(
        'tab_id' => 'tab-expired',
        'title' => 'Expired',
        'tone' => 'expired',
        'icon' => 'fa-calendar-times-o',
        'accent' => '#dc2626',
        'badge' => 'ar-badge-unread',
        'desc' => 'Expired certificates due for renewal/recertification.'
    ),
    'Withdrawn' => array(
        'tab_id' => 'tab-withdrawn',
        'title' => 'Withdrawn',
        'tone' => 'withdrawn',
        'icon' => 'fa-sign-out',
        'accent' => '#9333ea',
        'badge' => 'ar-badge-job',
        'desc' => 'Withdrawn clients for reconnection & win-back.'
    ),
    'Application canceled' => array(
        'tab_id' => 'tab-canceled',
        'title' => 'Canceled',
        'tone' => 'canceled',
        'icon' => 'fa-ban',
        'accent' => '#475569',
        'badge' => 'ar-badge-info',
        'desc' => 'Canceled applications to capture feedback or revive.'
    )
);

$sectionClients = array();
foreach ($statusSections as $key => $meta) {
    $sectionClients[$key] = array();
}

$dbError = '';
$allMarketingClients = array();

if (!$db) {
    $dbError = 'Database connection is not available.';
} else {
    try {
        $customerColumns = marketingColumnMap($db, 'customer_info');
        $serviceColumns = marketingColumnMap($db, 'service');

        $marketingInfo = "SELECT
                ci.`id`,
                ci.`customer_name`,
                " . marketingFieldExpr($customerColumns, 'ci', 'unique_id', 'unique_id') . ",
                " . marketingFieldExpr($customerColumns, 'ci', 'audit_status', 'audit_status') . ",
                " . marketingFieldExpr($customerColumns, 'ci', 'contact_person', 'contact_person') . ",
                " . marketingFieldExpr($customerColumns, 'ci', 'contact', 'contact') . ",
                " . marketingFieldExpr($customerColumns, 'ci', 'email_id', 'email_id') . ",
                " . marketingFieldExpr($customerColumns, 'ci', 'created_at', 'created_at') . ",
                svc.`service_id`,
                " . marketingFieldExpr($serviceColumns, 'svc', 'service_type', 'service_type') . ",
                " . marketingFieldExpr($serviceColumns, 'svc', 'service_status', 'service_status') . ",
                svcdt.`action` AS latest_action,
                svcdt.`due_date` AS latest_due_date
            FROM customer_info ci
            LEFT JOIN (
                SELECT s.*
                FROM service s
                INNER JOIN (
                    SELECT customer_id, MAX(service_id) AS max_service_id
                    FROM service
                    GROUP BY customer_id
                ) latest_service ON latest_service.customer_id = s.customer_id
                AND latest_service.max_service_id = s.service_id
            ) svc ON ci.id = svc.customer_id
            LEFT JOIN (
                SELECT d.*
                FROM ca_service_dates d
                INNER JOIN (
                    SELECT service_id, MAX(action_id) AS max_action_id
                    FROM ca_service_dates
                    WHERE is_deleted = 'N'
                    GROUP BY service_id
                ) latest_date ON latest_date.service_id = d.service_id
                AND latest_date.max_action_id = d.action_id
            ) svcdt ON svc.service_id = svcdt.service_id
            WHERE (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
            ORDER BY ci.id DESC";

        $statement = $db->prepare($marketingInfo);
        $statement->execute();
        $allMarketingClients = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($allMarketingClients as $client) {
            $normalizedStatus = marketingNormalizeStatus(isset($client['audit_status']) ? $client['audit_status'] : '');
            $serviceStatus = trim((string) (isset($client['service_status']) ? $client['service_status'] : ''));
            $bucket = '';

            if ($normalizedStatus === 'Application canceled') {
                $bucket = 'Application canceled';
            } else if ($normalizedStatus === 'Suspended' || $normalizedStatus === 'Expired' || $normalizedStatus === 'Withdrawn') {
                $bucket = $normalizedStatus;
            } else if (empty($client['service_id'])) {
                $bucket = 'New Client';
            } else if (strtolower($serviceStatus) === 'marketing') {
                $bucket = 'Current Marketing';
            }

            if ($bucket !== '' && isset($sectionClients[$bucket])) {
                $client['marketing_bucket'] = $bucket;
                $sectionClients[$bucket][] = $client;
            }
        }
    } catch (Exception $ex) {
        error_log($ex->getMessage());
        $dbError = 'Unable to load marketing client data.';
    }
}

$totalActionClients = 0;
foreach ($sectionClients as $rows) {
    $totalActionClients += count($rows);
}

// Find first non-empty section for default active tab
$defaultTab = 'New Client';
foreach ($statusSections as $key => $meta) {
    if (!empty($sectionClients[$key])) {
        $defaultTab = $key;
        break;
    }
}
?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid mktg-container">

                <?php if ($dbError !== '') { ?>
                    <div class="alert alert-danger" style="border-radius: 10px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fa fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo marketingText($dbError); ?>
                    </div>
                <?php } ?>

                <!-- Compact Hero Header (masterCreator Style) -->
                <div class="mktg-hero-header">
                    <div class="mktg-hero-left">
                        <div class="mktg-hero-icon"><i class="fa fa-bullhorn"></i></div>
                        <div>
                            <h1 class="mktg-hero-title">Marketing Follow-up Desk</h1>
                        </div>
                    </div>
                    <div class="mktg-hero-stats">
                        <div class="mktg-stat-pill">
                            <span class="mktg-stat-icon icon-blue"><i class="fa fa-user-plus"></i></span>
                            <div>
                                <div class="mktg-stat-num"><?php echo count($sectionClients['New Client']); ?></div>
                                <div class="mktg-stat-lbl">New Enquiries</div>
                            </div>
                        </div>
                        <div class="mktg-stat-pill">
                            <span class="mktg-stat-icon icon-cyan"><i class="fa fa-bullhorn"></i></span>
                            <div>
                                <div class="mktg-stat-num"><?php echo count($sectionClients['Current Marketing']); ?></div>
                                <div class="mktg-stat-lbl">In Marketing</div>
                            </div>
                        </div>
                        <div class="mktg-stat-pill">
                            <span class="mktg-stat-icon icon-amber"><i class="fa fa-pause-circle"></i></span>
                            <div>
                                <div class="mktg-stat-num"><?php echo count($sectionClients['Suspended']); ?></div>
                                <div class="mktg-stat-lbl">Suspended</div>
                            </div>
                        </div>
                        <div class="mktg-stat-pill">
                            <span class="mktg-stat-icon icon-red"><i class="fa fa-calendar-times-o"></i></span>
                            <div>
                                <div class="mktg-stat-num"><?php echo count($sectionClients['Expired']); ?></div>
                                <div class="mktg-stat-lbl">Expired</div>
                            </div>
                        </div>
                        <div class="mktg-stat-pill" style="background: #f8fafc; border-color: #cbd5e1;">
                            <span class="mktg-stat-icon icon-purple"><i class="fa fa-users"></i></span>
                            <div>
                                <div class="mktg-stat-num"><?php echo $totalActionClients; ?></div>
                                <div class="mktg-stat-lbl">Total Leads</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clean Folder Tabs Navigation -->
                <div class="mktg-tabs">
                    <?php foreach ($statusSections as $sectionKey => $sectionMeta) { 
                        $count = count($sectionClients[$sectionKey]);
                        $isActive = ($sectionKey === $defaultTab);
                    ?>
                        <button type="button" class="mktg-tab <?php echo $isActive ? 'active' : ''; ?>" data-target="<?php echo $sectionMeta['tab_id']; ?>">
                            <i class="fa <?php echo $sectionMeta['icon']; ?>"></i> <?php echo $sectionMeta['title']; ?>
                            <span class="mktg-tab-badge <?php echo $count === 0 ? 'mktg-badge-empty' : ''; ?>"><?php echo $count; ?></span>
                        </button>
                    <?php } ?>
                </div>

                <!-- Tab Panes Content -->
                <div class="mktg-tab-content">
                    <?php foreach ($statusSections as $sectionKey => $sectionMeta) { 
                        $rows = $sectionClients[$sectionKey];
                        $isActive = ($sectionKey === $defaultTab);
                    ?>
                        <div class="mktg-tab-pane <?php echo $isActive ? 'active' : ''; ?>" id="<?php echo $sectionMeta['tab_id']; ?>">
                            <div class="mktg-card-header">
                                <h3 class="mktg-card-title">
                                    <i class="fa <?php echo $sectionMeta['icon']; ?>"></i> <?php echo $sectionMeta['title']; ?>
                                </h3>
                                <div class="mktg-card-header-stats">
                                    <span class="mktg-count-badge"><i class="fa fa-database"></i> <?php echo count($rows); ?> Clients</span>
                                </div>
                            </div>

                            <div class="mktg-card-body table-responsive">
                                <?php if (empty($rows)) { ?>
                                    <div style="text-align: center; padding: 45px 20px; color: #94a3b8;">
                                        <i class="fa <?php echo $sectionMeta['icon']; ?>" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                                        <div style="font-weight: 700; font-size: 15px; color: #64748b;">No clients found in <?php echo $sectionMeta['title']; ?></div>
                                        <div style="font-size: 12.5px; margin-top: 4px;">There are currently no records requiring follow-up in this category.</div>
                                    </div>
                                <?php } else { ?>
                                    <table class="table mktg-table table-hover datatable-mktg">
                                        <thead>
                                            <tr>
                                                <th style="width: 55px; text-align: center;">Sr.</th>
                                                <th>Client / Organization</th>
                                                <th>Contact Person &amp; Details</th>
                                                <th style="width: 140px; text-align: center;">Service &amp; Status</th>
                                                <th>Next Action / Follow-up</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                        $sr = 0;
                                        foreach ($rows as $client) { 
                                            $sr++;
                                            $email = trim((string) (isset($client['email_id']) ? $client['email_id'] : ''));
                                            $phone = trim((string) (isset($client['contact']) ? $client['contact'] : ''));
                                            $contactPerson = trim((string) (isset($client['contact_person']) ? $client['contact_person'] : ''));
                                            $displayStatus = $sectionKey === 'New Client' ? 'New Client' : ($sectionKey === 'Current Marketing' ? 'Marketing' : $sectionKey);
                                        ?>
                                            <tr>
                                                <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $sr; ?></td>
                                                <td>
                                                    <a class="mktg-org-link customerAccount" data-id="<?php echo (int) $client['id']; ?>" title="Open Client Account">
                                                        <?php echo marketingText($client['customer_name']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div>
                                                        <?php if ($contactPerson !== '') { ?>
                                                            <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                                                                <i class="fa fa-user-o" style="color: #64748b; margin-right: 4px;"></i><?php echo marketingText($contactPerson); ?>
                                                            </div>
                                                        <?php } ?>
                                                        <div style="display: flex; gap: 10px; font-size: 12px; margin-top: 2px; flex-wrap: wrap;">
                                                            <?php if ($phone !== '') { ?>
                                                                <a href="tel:<?php echo marketingText($phone); ?>" style="color: #0d4d8b; text-decoration: none; font-weight: 600;">
                                                                    <i class="fa fa-phone"></i> <?php echo marketingText($phone); ?>
                                                                </a>
                                                            <?php } ?>
                                                            <?php if ($email !== '') { ?>
                                                                <a href="mailto:<?php echo marketingText($email); ?>" style="color: #64748b; text-decoration: none;">
                                                                    <i class="fa fa-envelope-o"></i> <?php echo marketingText($email); ?>
                                                                </a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    <span class="mktg-badge <?php echo $sectionMeta['badge']; ?>">
                                                        <?php echo marketingText($displayStatus); ?>
                                                    </span>
                                                    <?php if (!empty($client['service_type'])) { ?>
                                                        <div style="font-size: 11px; font-weight: 700; color: #475569; margin-top: 3px;">
                                                            <i class="fa fa-cog"></i> <?php echo marketingText($client['service_type']); ?>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #334155; font-size: 12.5px;">
                                                        <?php echo !empty($client['latest_action']) ? marketingText($client['latest_action']) : 'Follow-up'; ?>
                                                    </div>
                                                    <?php if (!empty($client['latest_due_date'])) { ?>
                                                        <div style="font-size: 11.5px; color: #dc2626; font-weight: 700; margin-top: 2px;">
                                                            <i class="fa fa-clock-o"></i> Due: <?php echo marketingText(marketingDate($client['latest_due_date'])); ?>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* =====================================================================
   MARKETING FOLLOW-UP DESK — MODERN CLEAN SAAS DESIGN SYSTEM
   ===================================================================== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

body, #wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    background-color: #f8fafc !important;
    color: #0f172a !important;
    -webkit-font-smoothing: antialiased;
}

#page-content-wrapper .fa {
    font-family: 'FontAwesome' !important;
}

.mktg-container {
    max-width: 1540px;
    margin: 0 auto;
    padding: 14px 20px 60px 20px;
}

/* =====================================================================
   COMPACT HERO HEADER
   ===================================================================== */
.mktg-hero-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 18px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.mktg-hero-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
}

.mktg-hero-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mktg-hero-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(13, 77, 139, 0.25);
    flex-shrink: 0;
}

.mktg-hero-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
}

.mktg-hero-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.mktg-stat-pill {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    gap: 9px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.15s, box-shadow 0.15s;
}

.mktg-stat-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.04);
}

.mktg-stat-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.mktg-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
.mktg-stat-icon.icon-cyan { background: #ecfeff; color: #0891b2; }
.mktg-stat-icon.icon-amber { background: #fffbeb; color: #d97706; }
.mktg-stat-icon.icon-red { background: #fef2f2; color: #dc2626; }
.mktg-stat-icon.icon-purple { background: #faf5ff; color: #9333ea; }

.mktg-stat-num {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}

.mktg-stat-lbl {
    font-size: 9.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    line-height: 1;
    margin-top: 2px;
}

/* =====================================================================
   FOLDER TABS NAVIGATION
   ===================================================================== */
.mktg-tabs {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    margin-bottom: 0;
    border-bottom: 2px solid #e2e8f0;
    padding-left: 4px;
    overflow-x: auto;
}

.mktg-tab {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    margin-bottom: -2px;
}

.mktg-tab:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.mktg-tab.active {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
    color: #ffffff !important;
    border-color: #0d4d8b !important;
    box-shadow: 0 -2px 8px rgba(13, 77, 139, 0.2);
    z-index: 2;
}

.mktg-tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 800;
    padding: 0 6px;
    line-height: 1;
    background: #2563eb;
    color: #ffffff;
}

.mktg-tab-badge.mktg-badge-empty {
    background: #cbd5e1;
    color: #475569;
}

.mktg-tab.active .mktg-tab-badge {
    background: #ffffff;
    color: #0d4d8b;
}

/* =====================================================================
   TAB CONTENT & CARDS
   ===================================================================== */
.mktg-tab-pane {
    display: none;
    animation: mktgFadeIn 0.2s ease-out;
}

.mktg-tab-pane.active {
    display: block;
}

@keyframes mktgFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.mktg-card-header {
    background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
    border-radius: 0 12px 0 0 !important;
    padding: 12px 20px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(13, 77, 139, 0.15);
}

.mktg-card-title {
    font-size: 13.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.mktg-card-header-stats {
    display: flex;
    gap: 8px;
    align-items: center;
}

.mktg-count-badge {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    color: #ffffff;
}

.mktg-card-body {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 0;
    margin-bottom: 24px;
    overflow: hidden;
}

/* =====================================================================
   TABLE DESIGN
   ===================================================================== */
.mktg-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.mktg-table thead th {
    background: #f8fafc !important;
    border: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    padding: 12px 16px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    color: #475569 !important;
    vertical-align: middle !important;
}

.mktg-table tbody td {
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    padding: 12px 16px !important;
    font-size: 13px !important;
    color: #334155 !important;
    vertical-align: middle !important;
    transition: background 0.15s ease !important;
}

.mktg-table tbody tr:last-child td {
    border-bottom: none !important;
}

.mktg-table tbody tr:hover td {
    background: #f8fafc !important;
}

.mktg-org-link {
    font-weight: 700;
    color: #0d4d8b;
    text-decoration: none;
    cursor: pointer;
    font-size: 13.5px;
    transition: color 0.15s;
}

.mktg-org-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Badges & Pills */
.mktg-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1;
}

.ar-badge-unread { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
.ar-badge-audit { background: #dbeafe; color: #1e40af; }
.ar-badge-nc { background: #cffafe; color: #0891b2; }
.ar-badge-info { background: #d1fae5; color: #065f46; }
.ar-badge-job { background: #ede9fe; color: #5b21b6; }
.ar-badge-auditor { background: #fef3c7; color: #92400e; }

/* =====================================================================
   DARK MODE SUPPORT
   ===================================================================== */
html.theme-dark {
    --hpd-bg: #0a0e14;
    --hpd-surface: #111722;
    --hpd-surface-2: #161d2b;
    --hpd-border: #232c3d;
    --hpd-border-2: #2e3a4f;
    --hpd-text: #dbe3f0;
    --hpd-text-dim: #93a1b8;
    --hpd-text-faint: #6b7890;
    --hpd-accent: #4a9eff;
}

html.theme-dark body, 
html.theme-dark #page-content-wrapper,
html.theme-dark .page-content.inset {
    background: var(--hpd-bg) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .mktg-hero-header {
    background: linear-gradient(135deg, #111722 0%, #0d131e 100%);
    border-color: var(--hpd-border);
}
html.theme-dark .mktg-hero-title { color: #f8fafc; }
html.theme-dark .mktg-stat-pill {
    background: var(--hpd-surface);
    border-color: var(--hpd-border);
}
html.theme-dark .mktg-stat-num { color: #f8fafc; }

html.theme-dark .mktg-tabs {
    border-bottom-color: var(--hpd-border);
}

html.theme-dark .mktg-tab {
    background: var(--hpd-surface-2);
    border-color: var(--hpd-border);
    color: var(--hpd-text-dim);
}

html.theme-dark .mktg-tab:hover {
    background: var(--hpd-surface);
    color: var(--hpd-text);
}

html.theme-dark .mktg-tab.active {
    background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
    border-color: var(--hpd-border-2) !important;
    color: #60a5fa !important;
}

html.theme-dark .mktg-card-header {
    background: linear-gradient(135deg, #16243d 0%, #0d1b33 100%) !important;
    border-color: var(--hpd-border-2);
}

html.theme-dark .mktg-card-body {
    background: var(--hpd-surface);
    border-color: var(--hpd-border);
}

html.theme-dark .mktg-table thead th {
    background: var(--hpd-surface-2) !important;
    border-bottom-color: var(--hpd-border) !important;
    color: var(--hpd-text-dim) !important;
}

html.theme-dark .mktg-table tbody td {
    border-bottom-color: var(--hpd-border) !important;
    color: var(--hpd-text) !important;
}

html.theme-dark .mktg-table tbody tr:hover td {
    background: var(--hpd-surface-2) !important;
}

html.theme-dark .mktg-org-link {
    color: #60a5fa;
}
</style>

<script type="text/javascript">
$(document).ready(function () {
    // Tab switching functionality
    $('.mktg-tab').on('click', function() {
        $('.mktg-tab').removeClass('active');
        $(this).addClass('active');

        var targetId = $(this).attr('data-target');
        $('.mktg-tab-pane').removeClass('active');
        $('#' + targetId).addClass('active');

        if ($.fn.DataTable) {
            $('#' + targetId + ' .datatable-mktg').DataTable().columns.adjust();
        }
    });

    // Client account navigation
    $(document).on('click', '.customerAccount', function () {
        var customerID = $(this).attr('data-id');
        if (!customerID) {
            return;
        }
        localStorage['visited'] = customerID;
        var form = $('<form action="customerAccount.php" method="get">' +
                '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                '</form>');
        $('body').append(form);
        form.submit();
    });

    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("active");
        $(".container").toggleClass("active");
        if ($.fn.DataTable) {
            $('.datatable-mktg').DataTable().columns.adjust();
        }
    });

    // Initialize lightweight DataTables for each pane
    if ($.fn.DataTable) {
        $('.datatable-mktg').DataTable({
            scrollX: true,
            paging: true,
            pageLength: 25,
            ordering: true,
            lengthChange: false,
            info: true,
            language: {
                search: "Filter leads:",
                paginate: {
                    next: '<i class="fa fa-chevron-right"></i>',
                    previous: '<i class="fa fa-chevron-left"></i>'
                }
            }
        });
    }
});
</script>

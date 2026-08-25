<div id="wrapper" class="active">
<?php
$pageIdentifier = "Statement Page";
include 'header.php';
include 'dbConnect.php';

// Fetch clients and payment data from both old payment_system and new statement_payments tables
$query = "
SELECT ci.id, ci.customer_name, ci.contact_person, ci.contact, ci.email_id, ci.audit_status,
       COALESCE(old_pay.total_decided, 0) + COALESCE(new_pay.total_decided, 0) as total_decided,
       COALESCE(old_pay.total_received, 0) + COALESCE(new_pay.total_received, 0) as total_received,
       COALESCE(old_pay.total_balance, 0) + COALESCE(new_pay.total_balance, 0) as total_balance,
       COALESCE(new_pay.total_tds_decided, 0) as total_tds_decided,
       COALESCE(new_pay.total_tds_received, 0) as total_tds_received,
       CASE WHEN new_pay.cnt > 0 THEN 1 ELSE 0 END as has_new_payments,
       CASE WHEN old_pay.cnt > 0 THEN 1 ELSE 0 END as has_old_payments
FROM customer_info ci
LEFT JOIN (
    SELECT customer_id, 
           SUM(amount_decided) as total_decided, 
           SUM(payment_received) as total_received, 
           SUM(balance) as total_balance, 
           COUNT(*) as cnt 
    FROM payment_system 
    WHERE service_type NOT LIKE '%tds%' 
    GROUP BY customer_id
) old_pay ON ci.id = old_pay.customer_id
LEFT JOIN (
    SELECT customer_id, 
           SUM(amount_decided) as total_decided, 
           SUM(payment_received) as total_received, 
           SUM(balance) as total_balance,
           SUM(tds_decided) as total_tds_decided,
           SUM(tds_received) as total_tds_received,
           COUNT(*) as cnt 
    FROM statement_payments 
    GROUP BY customer_id
) new_pay ON ci.id = new_pay.customer_id
WHERE ci.is_deleted = 'NO'
ORDER BY ci.id DESC
";

$stmt = $db->query($query);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$global_decided = 0;
$global_received = 0;
$global_balance = 0;
$global_clients = count($clients);
$count_new = 0;
$count_paid = 0;
$count_unpaid = 0;

foreach($clients as $c) {
    $dec = (float)$c['total_decided'];
    $rec = (float)$c['total_received'];
    $bal = (float)$c['total_balance'];
    $has_old = (int)$c['has_old_payments'];
    $has_new = (int)$c['has_new_payments'];

    $global_decided += $dec;
    $global_received += $rec;
    $global_balance += $bal;

    if (!$has_old && !$has_new) {
        $count_new++;
    } elseif ($bal <= 0 && ($has_old || $has_new)) {
        $count_paid++;
    } elseif ($bal > 0) {
        $count_unpaid++;
    }
}
?>

<!-- Premium Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   STATEMENT PAGE - COMPACT SLEEK STYLING
   ========================================================================== */
:root {
    --stmt-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --stmt-mono: 'JetBrains Mono', monospace;
    --stmt-primary: #0d4d8b;
    --stmt-primary-dark: #093766;
    --stmt-emerald: #059669;
    --stmt-rose: #dc2626;
    --stmt-amber: #d97706;
    --stmt-indigo: #4f46e5;
    --stmt-border: #e2e8f0;
}

body, #page-content-wrapper {
    font-family: var(--stmt-font) !important;
    background-color: #f4f7fb !important;
    color: #1e293b;
    -webkit-font-smoothing: antialiased;
}

/* Container */
.stmt-container {
    max-width: 1560px;
    margin: 0 auto;
    padding: 6px 20px 40px 20px;
}

/* Compact Page Header */
.stmt-page-header {
    margin-bottom: 12px;
}
.stmt-main-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.stmt-main-title i {
    color: #0d4d8b;
    font-size: 15px;
}

/* Compact KPI Grid */
.stmt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
@media (max-width: 1080px) {
    .stmt-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .stmt-kpi-grid { grid-template-columns: 1fr; }
}

.stmt-kpi-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 10px 14px;
    border: 1px solid #eef2f6;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.stmt-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
}
.stmt-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}
.stmt-kpi-card.decided::before { background: #3b82f6; }
.stmt-kpi-card.received::before { background: #10b981; }
.stmt-kpi-card.balance::before { background: #dc2626; }
.stmt-kpi-card.clients::before { background: #8b5cf6; }

.kpi-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}
.kpi-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}
.kpi-icon-pill {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}
.stmt-kpi-card.decided .kpi-icon-pill { background: #eff6ff; color: #2563eb; }
.stmt-kpi-card.received .kpi-icon-pill { background: #ecfdf5; color: #059669; }
.stmt-kpi-card.balance .kpi-icon-pill { background: #fef2f2; color: #dc2626; }
.stmt-kpi-card.clients .kpi-icon-pill { background: #f5f3ff; color: #7c3aed; }

.kpi-value {
    font-family: var(--stmt-mono) !important;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
    letter-spacing: -0.3px;
    line-height: 1.2;
}
.stmt-kpi-card.received .kpi-value { color: #059669; }
.stmt-kpi-card.balance .kpi-value { color: #dc2626; }

/* Compact Control Bar */
.stmt-control-bar {
    background: #ffffff;
    border-radius: 10px;
    padding: 8px 12px;
    border: 1px solid #eef2f6;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

/* Compact Segmented Tabs */
.stmt-segmented-tabs {
    display: inline-flex;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 8px;
    gap: 3px;
}
.stmt-tab-btn {
    border: none;
    background: transparent;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--stmt-font);
}
.stmt-tab-btn:hover { color: #0f172a; }
.stmt-tab-btn.active {
    background: #ffffff;
    color: #0d4d8b;
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}
.tab-badge {
    padding: 1px 6px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
}
.stmt-tab-btn.active .tab-badge {
    background: #0d4d8b;
    color: #ffffff;
}

/* Compact Search Box */
.stmt-search-box {
    position: relative;
    min-width: 260px;
    flex: 1;
    max-width: 380px;
}
.stmt-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #0d4d8b;
    font-size: 13px;
}
.stmt-search-box input {
    width: 100%;
    height: 34px;
    padding: 4px 10px 4px 32px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 12.5px;
    color: #1e293b;
    font-family: var(--stmt-font);
    box-sizing: border-box;
}
.stmt-search-box input:focus {
    outline: none;
    border-color: #0d4d8b;
    background: #ffffff;
    box-shadow: 0 0 0 2.5px rgba(13, 77, 139, 0.12);
}

/* Table Card Container */
.stmt-table-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #eef2f6;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
    overflow: hidden;
}

/* Main Table Styling */
.stmt-data-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    margin-bottom: 0 !important;
}
.stmt-data-table thead th {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
    color: #334155 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 10px 12px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap;
}
.stmt-data-table tbody td {
    padding: 10px 12px !important;
    border-bottom: 1px solid #f1f5f9 !important;
    vertical-align: middle !important;
    font-size: 13px !important;
    color: #1e293b;
}
.stmt-data-table tbody tr {
    cursor: pointer;
    transition: all 0.15s ease;
}
.stmt-data-table tbody tr:hover {
    background-color: #f8fbff !important;
}
.stmt-data-table tbody tr:hover td:first-child {
    box-shadow: inset 3px 0 0 #0d4d8b;
}

.cell-client-name {
    font-weight: 600;
    color: #0f172a;
    font-size: 13.5px;
}
.client-contact-info {
    font-size: 12.5px;
    color: #475569;
}
.client-phone {
    font-size: 12px;
    color: #64748b;
    font-family: var(--stmt-mono);
}

/* Numeric Currency Cells */
.cell-money {
    font-family: var(--stmt-mono) !important;
    font-weight: 700;
    font-size: 13px;
    text-align: right;
    white-space: nowrap;
}
.money-decided { color: #1e293b; }
.money-received { color: #059669; }
.money-balance { color: #dc2626; font-weight: 800; }
.money-balance.zero { color: #10b981; font-weight: 600; }

/* Status Badges */
.stmt-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 14px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}
.stmt-status-badge .dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
}
.badge-paid {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.badge-paid .dot { background: #10b981; }

.badge-unpaid {
    background: #fef2f2;
    color: #be123c;
    border: 1px solid #fecaca;
}
.badge-unpaid .dot { background: #ef4444; }

.badge-new {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.badge-new .dot { background: #3b82f6; }

.badge-norec {
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
.badge-norec .dot { background: #94a3b8; }

/* Action Arrow Pill */
.btn-open-stmt {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #0d4d8b;
    border: 1px solid #e2e8f0;
    font-size: 11px;
}
.stmt-data-table tbody tr:hover .btn-open-stmt {
    background: #0d4d8b;
    color: #ffffff;
    border-color: #0d4d8b;
}

/* DataTables Scroll & Filter Overrides */
.dataTables_wrapper .dataTables_filter { display: none; }
.dataTables_scrollBody { border-bottom: 1px solid #e2e8f0 !important; }

/* ==========================================================================
   DARK MODE SUPPORT
   ========================================================================== */
html.theme-dark body, 
html.theme-dark #page-content-wrapper {
    background-color: #0a0e17 !important;
    color: #dbe3f0 !important;
}
html.theme-dark .stmt-main-title { color: #ffffff; }
html.theme-dark .stmt-kpi-card {
    background: #111827;
    border-color: #1f293d;
}
html.theme-dark .kpi-value { color: #f8fafc; }
html.theme-dark .stmt-kpi-card.received .kpi-value { color: #34d399; }
html.theme-dark .stmt-kpi-card.balance .kpi-value { color: #f87171; }

html.theme-dark .stmt-control-bar,
html.theme-dark .stmt-table-card {
    background: #111827;
    border-color: #1f293d;
}
html.theme-dark .stmt-segmented-tabs {
    background: #1e293b;
}
html.theme-dark .stmt-tab-btn { color: #94a3b8; }
html.theme-dark .stmt-tab-btn.active {
    background: #0f172a;
    color: #60a5fa;
}
html.theme-dark .tab-badge {
    background: #334155;
    color: #cbd5e1;
}
html.theme-dark .stmt-tab-btn.active .tab-badge {
    background: #2563eb;
    color: #ffffff;
}

html.theme-dark .stmt-search-box input {
    background: #1e293b;
    border-color: #334155;
    color: #f8fafc;
}
html.theme-dark .stmt-search-box input:focus {
    border-color: #3b82f6;
    background: #0f172a;
}

html.theme-dark .stmt-data-table thead th {
    background: linear-gradient(180deg, #161f30 0%, #111827 100%) !important;
    color: #94a3b8 !important;
    border-bottom-color: #1f293d !important;
}
html.theme-dark .stmt-data-table tbody td {
    border-bottom-color: #1f293d !important;
    color: #dbe3f0;
}
html.theme-dark .cell-client-name { color: #ffffff; }
html.theme-dark .client-contact-info { color: #94a3b8; }
html.theme-dark .client-phone { color: #64748b; }

html.theme-dark .stmt-data-table tbody tr:hover {
    background-color: #162032 !important;
}
html.theme-dark .stmt-data-table tbody tr:hover td:first-child {
    box-shadow: inset 3px 0 0 #3b82f6;
}

html.theme-dark .money-decided { color: #f8fafc; }
html.theme-dark .money-received { color: #34d399; }
html.theme-dark .money-balance { color: #f87171; }
html.theme-dark .money-balance.zero { color: #34d399; }

html.theme-dark .badge-paid {
    background: rgba(5, 150, 105, 0.15);
    color: #34d399;
    border-color: rgba(5, 150, 105, 0.3);
}
html.theme-dark .badge-unpaid {
    background: rgba(220, 38, 38, 0.15);
    color: #f87171;
    border-color: rgba(220, 38, 38, 0.3);
}
html.theme-dark .badge-new {
    background: rgba(37, 99, 235, 0.15);
    color: #60a5fa;
    border-color: rgba(37, 99, 235, 0.3);
}
html.theme-dark .badge-norec {
    background: rgba(100, 116, 139, 0.15);
    color: #94a3b8;
    border-color: rgba(100, 116, 139, 0.3);
}
html.theme-dark .btn-open-stmt {
    background: #1e293b;
    border-color: #334155;
    color: #93c5fd;
}
html.theme-dark .stmt-data-table tbody tr:hover .btn-open-stmt {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
</style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="stmt-container">

                <!-- Compact Page Header -->
                <div class="stmt-page-header">
                    <h3 class="stmt-main-title"><i class="fa fa-file-text-o"></i> Payment Statements</h3>
                </div>

                <!-- 4 Compact KPI Summary Cards -->
                <div class="stmt-kpi-grid">
                    <div class="stmt-kpi-card decided">
                        <div class="kpi-head">
                            <span class="kpi-label">Total Decided</span>
                            <div class="kpi-icon-pill"><i class="fa fa-calculator"></i></div>
                        </div>
                        <div class="kpi-value">₹ <?php echo number_format($global_decided, 2); ?></div>
                    </div>

                    <div class="stmt-kpi-card received">
                        <div class="kpi-head">
                            <span class="kpi-label">Total Received</span>
                            <div class="kpi-icon-pill"><i class="fa fa-check-circle"></i></div>
                        </div>
                        <div class="kpi-value">₹ <?php echo number_format($global_received, 2); ?></div>
                    </div>

                    <div class="stmt-kpi-card balance">
                        <div class="kpi-head">
                            <span class="kpi-label">Total Balance Due</span>
                            <div class="kpi-icon-pill"><i class="fa fa-clock-o"></i></div>
                        </div>
                        <div class="kpi-value">₹ <?php echo number_format($global_balance, 2); ?></div>
                    </div>

                    <div class="stmt-kpi-card clients">
                        <div class="kpi-head">
                            <span class="kpi-label">Total Clients</span>
                            <div class="kpi-icon-pill"><i class="fa fa-building"></i></div>
                        </div>
                        <div class="kpi-value"><?php echo number_format($global_clients); ?></div>
                    </div>
                </div>

                <!-- Compact Control Bar: Filter Tabs + Live Search -->
                <div class="stmt-control-bar">
                    <div class="stmt-segmented-tabs">
                        <button class="stmt-tab-btn active" data-filter="all">
                            All Clients <span class="tab-badge"><?php echo $global_clients; ?></span>
                        </button>
                        <button class="stmt-tab-btn" data-filter="new">
                            New Clients <span class="tab-badge"><?php echo $count_new; ?></span>
                        </button>
                        <button class="stmt-tab-btn" data-filter="paid">
                            Paid <span class="tab-badge"><?php echo $count_paid; ?></span>
                        </button>
                        <button class="stmt-tab-btn" data-filter="unpaid">
                            Unpaid <span class="tab-badge"><?php echo $count_unpaid; ?></span>
                        </button>
                    </div>

                    <div class="stmt-search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="statementSearch" placeholder="Search by Client Name, Contact, Phone..." autocomplete="off">
                    </div>
                </div>

                <!-- Statements Table Card -->
                <div class="stmt-table-card">
                    <div class="table-responsive">
                        <table id="statementTable" class="table stmt-data-table">
                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="32%">Client Name</th>
                                    <th width="16%">Contact Person</th>
                                    <th width="12%">Phone</th>
                                    <th width="12%" style="text-align: right;">Total Decided</th>
                                    <th width="12%" style="text-align: right;">Total Received</th>
                                    <th width="11%" style="text-align: right;">Balance</th>
                                    <th width="11%" style="text-align: center;">Status</th>
                                    <th width="4%" style="text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach($clients as $c) {
                                    $dec = (float)$c['total_decided'];
                                    $rec = (float)$c['total_received'];
                                    $bal = (float)$c['total_balance'];
                                    $has_old = (int)$c['has_old_payments'];
                                    $has_new = (int)$c['has_new_payments'];

                                    // Status classification
                                    if (!$has_old && !$has_new) {
                                        $statusKey = 'new';
                                        $badgeHtml = '<span class="stmt-status-badge badge-new"><span class="dot"></span> New Client</span>';
                                    } elseif ($bal <= 0 && ($has_old || $has_new)) {
                                        $statusKey = 'paid';
                                        $badgeHtml = '<span class="stmt-status-badge badge-paid"><span class="dot"></span> Fully Paid</span>';
                                    } elseif ($bal > 0) {
                                        $statusKey = 'unpaid';
                                        $badgeHtml = '<span class="stmt-status-badge badge-unpaid"><span class="dot"></span> Due Payment</span>';
                                    } else {
                                        $statusKey = 'new';
                                        $badgeHtml = '<span class="stmt-status-badge badge-norec"><span class="dot"></span> No Record</span>';
                                    }
                                ?>
                                    <tr data-status="<?php echo $statusKey; ?>" data-id="<?php echo $c['id']; ?>">
                                        <td style="color: #94a3b8; font-weight: 600; font-family: var(--stmt-mono);"><?php echo $counter++; ?></td>
                                        <td>
                                            <div class="cell-client-name">
                                                <span><?php echo htmlspecialchars($c['customer_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="client-contact-info"><?php echo !empty($c['contact_person']) ? htmlspecialchars($c['contact_person']) : '<span style="color:#cbd5e1;">—</span>'; ?></span>
                                        </td>
                                        <td>
                                            <span class="client-phone"><?php echo !empty($c['contact']) ? htmlspecialchars($c['contact']) : '<span style="color:#cbd5e1;">—</span>'; ?></span>
                                        </td>
                                        <td class="cell-money money-decided">
                                            ₹ <?php echo number_format($dec, 2); ?>
                                        </td>
                                        <td class="cell-money money-received">
                                            ₹ <?php echo number_format($rec, 2); ?>
                                        </td>
                                        <td class="cell-money money-balance <?php echo ($bal <= 0) ? 'zero' : ''; ?>">
                                            ₹ <?php echo number_format($bal, 2); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php echo $badgeHtml; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="btn-open-stmt" title="Open Client Statement">
                                                <i class="fa fa-chevron-right"></i>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTables
    var table = $('#statementTable').DataTable({
        "sScrollX": "100%",
        "scrollY": "62vh",
        "scrollCollapse": true,
        "paging": false,
        "ordering": true,
        "order": [[0, "asc"]],
        "info": false,
        "language": {
            "emptyTable": "No statement records found"
        },
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ]
    });

    // Custom Live Search with debounce
    var searchTimer;
    $('#statementSearch').on('input', function() {
        clearTimeout(searchTimer);
        var val = $(this).val();
        searchTimer = setTimeout(function() {
            table.search(val).draw();
        }, 150);
    });

    // Segmented Filter Tabs
    var currentFilter = 'all';
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'statementTable') return true;
            if (currentFilter === 'all') return true;

            var rowNode = table.row(dataIndex).node();
            var rowStatus = $(rowNode).attr('data-status');
            return rowStatus === currentFilter;
        }
    );

    $('.stmt-tab-btn').on('click', function() {
        $('.stmt-tab-btn').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        table.draw();
    });

    // Row Click -> Open statementDetail.php
    $('#statementTable tbody').on('click', 'tr', function() {
        var id = $(this).data('id');
        if (id) {
            window.location.href = 'statementDetail.php?id=' + id;
        }
    });

    // Menu toggle support
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("active");
        setTimeout(function() {
            table.columns.adjust().draw();
        }, 300);
    });

    // Adjust columns on load & window resize
    setTimeout(function() { table.columns.adjust().draw(); }, 150);
    $(window).on('resize', function() { table.columns.adjust().draw(); });
});
</script>

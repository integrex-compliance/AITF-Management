<div id="wrapper" class="active">
<?php
$pageIdentifier = "Statement Page";
include 'header.php';
include 'dbConnect.php';

$customerId = $_GET['id'] ?? '';
if (empty($customerId)) {
    header('Location: statementPage.php');
    exit;
}

// Client info
$stmt = $db->prepare("SELECT * FROM customer_info WHERE id = :id");
$stmt->execute([':id' => $customerId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) { 
    header('Location: statementPage.php'); 
    exit; 
}

// Old payment records from legacy payment_system
$stmt = $db->prepare("
    SELECT ps.* 
    FROM payment_system ps 
    WHERE ps.customer_id = :cid 
      AND ps.service_type NOT LIKE '%tds%' 
    ORDER BY ps.payment_id ASC
");
$stmt->execute([':cid' => $customerId]);
$oldPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// New statement payments
$stmt = $db->prepare("
    SELECT * 
    FROM statement_payments 
    WHERE customer_id = :cid 
    ORDER BY id ASC
");
$stmt->execute([':cid' => $customerId]);
$newPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bank reconciliation records
$stmt = $db->prepare("
    SELECT * 
    FROM statement_bank_reconciliation 
    WHERE customer_id = :cid 
    ORDER BY acknowledged_at DESC
");
$stmt->execute([':cid' => $customerId]);
$reconciliations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$totalDecided = 0; 
$totalReceived = 0; 
$totalBalance = 0; 
$totalTdsDecided = 0; 
$totalTdsReceived = 0;

foreach ($oldPayments as $p) {
    $totalDecided += floatval($p['amount_decided']);
    $totalReceived += floatval($p['payment_received']);
    $totalBalance += floatval($p['balance']);
}
foreach ($newPayments as $p) {
    $totalDecided += floatval($p['amount_decided']);
    $totalReceived += floatval($p['payment_received']);
    $totalBalance += floatval($p['balance']);
    $totalTdsDecided += floatval($p['tds_decided'] ?? 0);
    $totalTdsReceived += floatval($p['tds_received'] ?? 0);
}

// Ensure at least 5 rows by default
$totalExistingRows = count($oldPayments) + count($newPayments);
$defaultBlankRowsNeeded = max(0, 5 - $totalExistingRows);
?>

<!-- Premium Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   STATEMENT DETAIL PAGE - ULTRA CLEAN COMPACT STYLING
   ========================================================================== */
:root {
    --stmt-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --stmt-mono: 'JetBrains Mono', monospace;
    --stmt-primary: #0d4d8b;
    --stmt-primary-dark: #093766;
    --stmt-emerald: #059669;
    --stmt-rose: #dc2626;
    --stmt-amber: #d97706;
    --stmt-purple: #7c3aed;
    --stmt-cyan: #0284c7;
    --stmt-border: #e2e8f0;
}

body, #page-content-wrapper {
    font-family: var(--stmt-font) !important;
    background-color: #f4f7fb !important;
    color: #1e293b;
    -webkit-font-smoothing: antialiased;
}

.stmt-detail-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 6px 20px 40px 20px;
}

/* ==========================================================================
   COMPACT CLIENT HEADER CARD
   ========================================================================== */
.stmt-hero-compact {
    background: #ffffff;
    border-radius: 12px;
    padding: 14px 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.hero-compact-left {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.hero-compact-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.hero-compact-title h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.2px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.hero-compact-title h3 i {
    color: #0d4d8b;
    font-size: 15px;
}

.hero-compact-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #64748b;
}
.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f8fafc;
    padding: 2px 8px;
    border-radius: 6px;
    border: 1px solid #eef2f6;
}
.meta-chip i {
    color: #0d4d8b;
    font-size: 11px;
}

.hero-compact-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Compact Buttons */
.btn-compact-bank {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-compact-bank:hover {
    background: #fef3c7;
    color: #b45309;
    text-decoration: none;
    transform: translateY(-1px);
}
.btn-compact-back {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-compact-back:hover {
    background: #e2e8f0;
    color: #1e293b;
    text-decoration: none;
    transform: translateY(-1px);
}

/* ==========================================================================
   5 FINANCIAL METRIC CARDS (COMPACT & NO WRAP)
   ========================================================================== */
.stmt-stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
@media (max-width: 1100px) {
    .stmt-stats-row { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 700px) {
    .stmt-stats-row { grid-template-columns: repeat(2, 1fr); }
}

.stmt-stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 12px 14px;
    border: 1px solid #eef2f6;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
    position: relative;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.stmt-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
}
.stmt-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}
.stmt-stat-card.c-decided::before { background: #3b82f6; }
.stmt-stat-card.c-received::before { background: #10b981; }
.stmt-stat-card.c-balance::before { background: #dc2626; }
.stmt-stat-card.c-tds-dec::before { background: #8b5cf6; }
.stmt-stat-card.c-tds-rec::before { background: #06b6d4; }

.s-card-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.s-card-value {
    font-family: var(--stmt-mono) !important;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.3px;
}
.stmt-stat-card.c-received .s-card-value { color: #059669; }
.stmt-stat-card.c-balance .s-card-value { color: #dc2626; }
.stmt-stat-card.c-tds-dec .s-card-value { color: #7c3aed; }
.stmt-stat-card.c-tds-rec .s-card-value { color: #0284c7; }

/* ==========================================================================
   MASTER PAYMENT TABLE CARD & TOOLBAR
   ========================================================================== */
.stmt-master-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.03);
    overflow: hidden;
}

.stmt-toolbar {
    background: #ffffff;
    padding: 12px 16px;
    border-bottom: 1px solid #eef2f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-action-add {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-action-add:hover {
    background: #059669;
    color: #ffffff;
    border-color: #059669;
    transform: translateY(-1px);
}
.btn-action-bank {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-action-bank:hover {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
    transform: translateY(-1px);
}
.toolbar-tip {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Master Table */
.stmt-detail-table {
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0;
    margin: 0;
}
.stmt-detail-table thead th {
    background: #0f2b48 !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 10px 8px !important;
    border: none !important;
    white-space: nowrap;
}
.stmt-detail-table tbody td {
    padding: 6px 8px !important;
    border-bottom: 1px solid #f1f5f9 !important;
    vertical-align: middle !important;
    font-size: 13px;
}

/* Legacy Rows (Read Only) */
.tr-legacy {
    background-color: #f8fafc !important;
}
.tr-legacy td {
    color: #475569;
}
.badge-source-legacy {
    background: #e2e8f0;
    color: #475569;
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.badge-source-new {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Inputs in Editable Rows */
.stmt-input {
    width: 100%;
    height: 32px;
    padding: 4px 8px;
    font-size: 12.5px;
    font-family: var(--stmt-font);
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.15s ease;
    box-sizing: border-box;
}
.stmt-input:focus {
    outline: none;
    border-color: #0d4d8b;
    box-shadow: 0 0 0 2.5px rgba(13, 77, 139, 0.12);
}
.stmt-input.num-field {
    font-family: var(--stmt-mono);
    text-align: right;
    font-weight: 600;
}
.stmt-input.balance-field {
    background: #f8fafc;
    font-weight: 700;
    color: #0f172a;
    cursor: default;
}
.stmt-input.tds-field {
    background: #fdf4ff;
    border-color: #f0abfc;
    color: #701a75;
}
.stmt-input.tds-field:focus {
    border-color: #c084fc;
    box-shadow: 0 0 0 2.5px rgba(192, 132, 252, 0.15);
}

/* Action Icon Buttons */
.stmt-row-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.btn-row-action {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s ease;
    background: #f1f5f9;
}
.btn-row-save { color: #059669; }
.btn-row-save:hover { background: #ecfdf5; border-color: #a7f3d0; }
.btn-row-delete { color: #dc2626; }
.btn-row-delete:hover { background: #fef2f2; border-color: #fecaca; }

/* Table Footer Totals */
.stmt-detail-table tfoot td {
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
    padding: 10px 8px !important;
    font-weight: 800;
    font-family: var(--stmt-mono);
    font-size: 13px;
    color: #0f172a;
    white-space: nowrap;
}

/* ==========================================================================
   BANK RECONCILIATION MODAL
   ========================================================================== */
.stmt-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.stmt-modal-box {
    background: #ffffff;
    border-radius: 16px;
    width: 560px;
    max-width: 100%;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
    overflow: hidden;
}
.stmt-modal-head {
    background: #0f2b48;
    color: #ffffff;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.stmt-modal-head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-modal-close {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #ffffff;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stmt-modal-body { padding: 20px; }
.reconcile-ack-card {
    background: #f0fdf4;
    border: 1.5px solid #86efac;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
}
.reconcile-ack-card label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #14532d;
    margin: 0;
}
.reconcile-ack-card input[type=checkbox] {
    width: 18px;
    height: 18px;
    accent-color: #059669;
    margin-top: 1px;
    cursor: pointer;
}

.file-upload-box { margin-bottom: 16px; }
.file-upload-box label {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    margin-bottom: 6px;
}
.file-upload-box input[type=file] {
    width: 100%;
    padding: 8px;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
    font-size: 12px;
}

.reconcile-history-section h4 {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #0d4d8b;
    margin: 0 0 8px 0;
}
.history-items-list {
    max-height: 160px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.history-item-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}
.btn-view-doc {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 3px 8px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 11px;
    text-decoration: none;
}

.stmt-modal-foot {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #eef2f6;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.btn-modal-cancel {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
}
.btn-modal-confirm {
    background: #059669;
    color: #ffffff;
    border: none;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.btn-modal-confirm:disabled { opacity: 0.5; cursor: not-allowed; }

/* Toast Notifications */
.stmt-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    z-index: 999999;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ==========================================================================
   DARK MODE
   ========================================================================== */
html.theme-dark body, 
html.theme-dark #page-content-wrapper {
    background-color: #0a0e17 !important;
    color: #dbe3f0 !important;
}
html.theme-dark .stmt-hero-compact,
html.theme-dark .stmt-stat-card,
html.theme-dark .stmt-master-card {
    background: #111827;
    border-color: #1f293d;
}
html.theme-dark .hero-compact-title h3 { color: #ffffff; }
html.theme-dark .meta-chip {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
html.theme-dark .btn-compact-back {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
html.theme-dark .s-card-value { color: #f8fafc; }
html.theme-dark .stmt-toolbar {
    background: #111827;
    border-bottom-color: #1f293d;
}
html.theme-dark .stmt-detail-table thead th {
    background: #162032 !important;
    border-bottom: 1px solid #1f293d !important;
}
html.theme-dark .stmt-detail-table tbody td {
    border-bottom-color: #1f293d !important;
    color: #dbe3f0;
}
html.theme-dark .tr-legacy {
    background-color: #161f30 !important;
}
html.theme-dark .tr-legacy td { color: #94a3b8; }
html.theme-dark .badge-source-legacy {
    background: #1e293b;
    color: #94a3b8;
}
html.theme-dark .stmt-input {
    background: #1e293b;
    border-color: #334155;
    color: #f8fafc;
}
html.theme-dark .stmt-input:focus {
    border-color: #3b82f6;
    background: #0f172a;
}
html.theme-dark .stmt-input.balance-field {
    background: #161f30;
    color: #f8fafc;
}
html.theme-dark .stmt-input.tds-field {
    background: rgba(124, 58, 237, 0.15);
    border-color: rgba(124, 58, 237, 0.3);
    color: #d8b4fe;
}
html.theme-dark .btn-row-action {
    background: #1e293b;
}
html.theme-dark .stmt-detail-table tfoot td {
    background: #161f30;
    border-top-color: #1f293d;
    color: #f8fafc;
}
html.theme-dark .stmt-modal-box {
    background: #111827;
    border: 1px solid #1f293d;
}
html.theme-dark .stmt-modal-foot {
    background: #161f30;
    border-top-color: #1f293d;
}
html.theme-dark .btn-modal-cancel {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}
html.theme-dark .history-item-row {
    background: #161f30;
    border-color: #1f293d;
}
html.theme-dark .reconcile-ack-card {
    background: rgba(5, 150, 105, 0.15);
    border-color: rgba(5, 150, 105, 0.3);
}
html.theme-dark .reconcile-ack-card label { color: #86efac; }
html.theme-dark .file-upload-box input[type=file] {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
</style>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="stmt-detail-container">

                <!-- Compact Client Header Card -->
                <div class="stmt-hero-compact">
                    <div class="hero-compact-left">
                        <div class="hero-compact-title">
                            <h3><i class="fa fa-building-o"></i> <?php echo htmlspecialchars($client['customer_name']); ?></h3>
                        </div>
                        <div class="hero-compact-meta">
                            <?php if(!empty($client['contact_person'])) { ?>
                                <span class="meta-chip"><i class="fa fa-user"></i> <?php echo htmlspecialchars($client['contact_person']); ?></span>
                            <?php } ?>
                            <?php if(!empty($client['contact'])) { ?>
                                <span class="meta-chip"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($client['contact']); ?></span>
                            <?php } ?>
                            <?php if(!empty($client['email_id'])) { ?>
                                <span class="meta-chip"><i class="fa fa-envelope-o"></i> <?php echo htmlspecialchars($client['email_id']); ?></span>
                            <?php } ?>
                            <?php if(!empty($client['audit_status'])) { ?>
                                <span class="meta-chip"><i class="fa fa-tag"></i> Stage: <?php echo htmlspecialchars($client['audit_status']); ?></span>
                            <?php } ?>
                            <?php if(!empty($client['gstin_no'])) { ?>
                                <span class="meta-chip"><i class="fa fa-file-text-o"></i> GSTIN: <?php echo htmlspecialchars($client['gstin_no']); ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="hero-compact-actions">
                        <a href="statementPage.php" class="btn-compact-back">
                            <i class="fa fa-arrow-left"></i> Back to Statements
                        </a>
                    </div>
                </div>

                <!-- 5 Financial Stat Cards (Compact & No-Wrap) -->
                <div class="stmt-stats-row">
                    <div class="stmt-stat-card c-decided">
                        <div class="s-card-label">
                            <span>Total Decided</span>
                            <i class="fa fa-calculator" style="color: #3b82f6;"></i>
                        </div>
                        <div class="s-card-value" id="kpiDecided">₹ <?php echo number_format($totalDecided, 2); ?></div>
                    </div>

                    <div class="stmt-stat-card c-received">
                        <div class="s-card-label">
                            <span>Total Received</span>
                            <i class="fa fa-check-circle" style="color: #10b981;"></i>
                        </div>
                        <div class="s-card-value" id="kpiReceived">₹ <?php echo number_format($totalReceived, 2); ?></div>
                    </div>

                    <div class="stmt-stat-card c-balance">
                        <div class="s-card-label">
                            <span>Outstanding Balance</span>
                            <i class="fa fa-clock-o" style="color: #ef4444;"></i>
                        </div>
                        <div class="s-card-value" id="kpiBalance">₹ <?php echo number_format($totalBalance, 2); ?></div>
                    </div>

                    <div class="stmt-stat-card c-tds-dec">
                        <div class="s-card-label">
                            <span>TDS Decided</span>
                            <i class="fa fa-pie-chart" style="color: #8b5cf6;"></i>
                        </div>
                        <div class="s-card-value" id="kpiTdsDec">₹ <?php echo number_format($totalTdsDecided, 2); ?></div>
                    </div>

                    <div class="stmt-stat-card c-tds-rec">
                        <div class="s-card-label">
                            <span>TDS Received</span>
                            <i class="fa fa-file-text-o" style="color: #06b6d4;"></i>
                        </div>
                        <div class="s-card-value" id="kpiTdsRec">₹ <?php echo number_format($totalTdsReceived, 2); ?></div>
                    </div>
                </div>

                <!-- Master Payment Table Card -->
                <div class="stmt-master-card">
                    <div class="stmt-toolbar">
                        <div class="toolbar-actions">
                            <button type="button" class="btn-action-add" id="btnAddNewRow">
                                <i class="fa fa-plus-circle"></i> Add Payment Row
                            </button>
                            <button type="button" class="btn-action-bank" id="openBankModalBtn">
                                <i class="fa fa-university"></i> Bank Reconciliation
                            </button>
                        </div>
                        <div class="toolbar-tip">
                            <i class="fa fa-info-circle" style="color: #0d4d8b;"></i>
                            <span>Save individual rows with the Save (💾) icon on the right.</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="stmt-detail-table" id="masterPaymentTable">
                            <thead>
                                <tr>
                                    <th width="4%" style="text-align: center;">#</th>
                                    <th width="16%">Service Type / Stage</th>
                                    <th width="13%" style="text-align: right;">Amount Decided (₹)</th>
                                    <th width="13%" style="text-align: right;">Payment Received (₹)</th>
                                    <th width="11%">Payment Date</th>
                                    <th width="13%">Instrument / Mode</th>
                                    <th width="11%" style="text-align: right;">Balance (₹)</th>
                                    <th width="10%" style="text-align: right;">TDS Decided (₹)</th>
                                    <th width="10%" style="text-align: right;">TDS Received (₹)</th>
                                    <th width="7%" style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rowCounter = 1;

                                // 1. Render Legacy Records (Read Only)
                                foreach ($oldPayments as $op) { 
                                    $dec = floatval($op['amount_decided']);
                                    $rec = floatval($op['payment_received']);
                                    $bal = floatval($op['balance']);
                                    $dateFormatted = !empty($op['payment_date']) ? date('d.m.Y', strtotime($op['payment_date'])) : '—';
                                ?>
                                    <tr class="tr-legacy" data-source="legacy">
                                        <td style="text-align: center; color: #94a3b8; font-weight: 600; font-family: var(--stmt-mono);"><?php echo $rowCounter++; ?></td>
                                        <td style="font-weight: 600; color: #1e293b;">
                                            <?php echo htmlspecialchars($op['service_type']); ?>
                                        </td>
                                        <td style="text-align: right; font-family: var(--stmt-mono); font-weight: 700;">
                                            ₹ <?php echo number_format($dec, 2); ?>
                                        </td>
                                        <td style="text-align: right; font-family: var(--stmt-mono); font-weight: 700; color: #059669;">
                                            ₹ <?php echo number_format($rec, 2); ?>
                                        </td>
                                        <td style="font-size: 13px;">
                                            <?php echo $dateFormatted; ?>
                                        </td>
                                        <td style="font-size: 13px;">
                                            <?php echo !empty($op['cheque_details']) ? htmlspecialchars($op['cheque_details']) : '—'; ?>
                                        </td>
                                        <td style="text-align: right; font-family: var(--stmt-mono); font-weight: 800; color: <?php echo ($bal > 0) ? '#dc2626' : '#059669'; ?>;">
                                            ₹ <?php echo number_format($bal, 2); ?>
                                        </td>
                                        <td style="text-align: right; color: #94a3b8; font-family: var(--stmt-mono);">—</td>
                                        <td style="text-align: right; color: #94a3b8; font-family: var(--stmt-mono);">—</td>
                                        <td style="text-align: center; color: #94a3b8;">
                                            <i class="fa fa-shield" title="Protected Legacy Entry" style="font-size: 15px;"></i>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <!-- 2. Render Existing New Editable Statement Payments -->
                                <?php foreach ($newPayments as $np) { 
                                    $dec = floatval($np['amount_decided']);
                                    $rec = floatval($np['payment_received']);
                                    $bal = floatval($np['balance']);
                                    $tdsDec = floatval($np['tds_decided'] ?? 0);
                                    $tdsRec = floatval($np['tds_received'] ?? 0);
                                    $dateVal = !empty($np['payment_date']) ? date('d.m.Y', strtotime($np['payment_date'])) : '';
                                ?>
                                    <tr class="tr-editable" data-source="new" data-id="<?php echo $np['id']; ?>">
                                        <td style="text-align: center; color: #64748b; font-weight: 600; font-family: var(--stmt-mono);" class="row-num"><?php echo $rowCounter++; ?></td>
                                        <td>
                                            <input type="text" class="stmt-input field-service" value="<?php echo htmlspecialchars($np['service_type']); ?>" placeholder="e.g. CA, SA-I, RC">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field field-decided" value="<?php echo $dec; ?>" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field field-received" value="<?php echo $rec; ?>" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input datepicker-stmt field-date" value="<?php echo $dateVal; ?>" placeholder="DD.MM.YYYY" autocomplete="off">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input field-instrument" value="<?php echo htmlspecialchars($np['instrument']); ?>" placeholder="NEFT / UTR / Cheque">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input num-field balance-field field-balance" value="<?php echo number_format($bal, 2, '.', ''); ?>" readonly>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-dec" value="<?php echo $tdsDec; ?>" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-rec" value="<?php echo $tdsRec; ?>" placeholder="0.00">
                                        </td>
                                        <td>
                                            <div class="stmt-row-actions">
                                                <button type="button" class="btn-row-action btn-row-save" title="Save this row">
                                                    <i class="fa fa-floppy-o"></i>
                                                </button>
                                                <button type="button" class="btn-row-action btn-row-delete" data-id="<?php echo $np['id']; ?>" title="Delete row">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <!-- 3. Render Default Blank Rows so at least 5 rows exist initially -->
                                <?php for ($b = 1; $b <= $defaultBlankRowsNeeded; $b++) { ?>
                                    <tr class="tr-editable" data-source="new" data-id="new_init_<?php echo $b; ?>">
                                        <td style="text-align: center; color: #64748b; font-weight: 600; font-family: var(--stmt-mono);" class="row-num"><?php echo $rowCounter++; ?></td>
                                        <td>
                                            <input type="text" class="stmt-input field-service" value="" placeholder="e.g. CA, SA-I, RC">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field field-decided" value="0.00" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field field-received" value="0.00" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input datepicker-stmt field-date" value="" placeholder="DD.MM.YYYY" autocomplete="off">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input field-instrument" value="" placeholder="NEFT / UTR / Cheque">
                                        </td>
                                        <td>
                                            <input type="text" class="stmt-input num-field balance-field field-balance" value="0.00" readonly>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-dec" value="0.00" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-rec" value="0.00" placeholder="0.00">
                                        </td>
                                        <td>
                                            <div class="stmt-row-actions">
                                                <button type="button" class="btn-row-action btn-row-save" title="Save this row">
                                                    <i class="fa fa-floppy-o"></i>
                                                </button>
                                                <button type="button" class="btn-row-action btn-row-delete" data-id="new_init_<?php echo $b; ?>" title="Delete row">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                            <!-- Footer Summary Totals -->
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-align: right; text-transform: uppercase; font-size: 11px; letter-spacing: 0.6px; color: #64748b;">
                                        Grand Totals:
                                    </td>
                                    <td style="text-align: right;" id="footDecided">₹ <?php echo number_format($totalDecided, 2); ?></td>
                                    <td style="text-align: right; color: #059669;" id="footReceived">₹ <?php echo number_format($totalReceived, 2); ?></td>
                                    <td colspan="2"></td>
                                    <td style="text-align: right; color: #dc2626;" id="footBalance">₹ <?php echo number_format($totalBalance, 2); ?></td>
                                    <td style="text-align: right; color: #7c3aed;" id="footTdsDec">₹ <?php echo number_format($totalTdsDecided, 2); ?></td>
                                    <td style="text-align: right; color: #0284c7;" id="footTdsRec">₹ <?php echo number_format($totalTdsReceived, 2); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     BANK RECONCILIATION MODAL
     ========================================================================== -->
<div id="bankReconcileModal" class="stmt-modal-overlay" style="display:none;">
    <div class="stmt-modal-box">
        <div class="stmt-modal-head">
            <h3><i class="fa fa-university"></i> Bank Statement Reconciliation</h3>
            <button type="button" class="btn-modal-close" id="closeBankModal">&times;</button>
        </div>
        <div class="stmt-modal-body">
            <div class="reconcile-ack-card">
                <label>
                    <input type="checkbox" id="acknowledgeCheck">
                    <span>I acknowledge that all the payment entries match against the bank statement.</span>
                </label>
            </div>

            <div class="file-upload-box">
                <label><i class="fa fa-paperclip"></i> Attach Official Bank Statement File:</label>
                <input type="file" id="bankStatementFile" accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls">
            </div>

            <?php if (!empty($reconciliations)) { ?>
            <div class="reconcile-history-section">
                <h4><i class="fa fa-history"></i> Previous Reconciliations:</h4>
                <div class="history-items-list">
                    <?php foreach ($reconciliations as $rec) { ?>
                    <div class="history-item-row">
                        <div>
                            <span style="font-weight: 700; color: #0f172a; font-family: var(--stmt-mono);"><?php echo date('d.m.Y H:i', strtotime($rec['acknowledged_at'])); ?></span>
                            <span style="color: #64748b; margin-left: 6px;">by <?php echo htmlspecialchars($rec['acknowledged_by'] ?? 'Admin'); ?></span>
                        </div>
                        <?php if (!empty($rec['bank_statement_file'])) { ?>
                        <a href="statementApi.php?action=view_bank_file&file_id=<?php echo $rec['id']; ?>" target="_blank" class="btn-view-doc" title="Open Statement Document">
                            <i class="fa fa-eye"></i> View Attachment
                        </a>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="stmt-modal-foot">
            <button type="button" class="btn-modal-cancel" id="cancelBankModal">Cancel</button>
            <button type="button" class="btn-modal-confirm" id="confirmReconcile" disabled>
                <i class="fa fa-check"></i> Confirm & Reconcile
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var customerId = '<?php echo $customerId; ?>';

    // Helper: Initialize Datepicker
    function initDatepickers() {
        if (typeof $.fn.datepicker !== 'undefined') {
            $('.datepicker-stmt').datepicker({
                dateFormat: 'dd.mm.yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '-20:+10'
            });
        }
    }
    initDatepickers();

    // Live Auto-Calculation of Balance on input
    $(document).on('input', '.field-decided, .field-received', function() {
        var $row = $(this).closest('tr');
        var decided = parseFloat($row.find('.field-decided').val()) || 0;
        var received = parseFloat($row.find('.field-received').val()) || 0;
        var balance = (decided - received).toFixed(2);
        $row.find('.field-balance').val(balance);
        recalcMasterSummary();
    });

    $(document).on('input', '.field-tds-dec, .field-tds-rec', function() {
        recalcMasterSummary();
    });

    // Recalculate Totals Across Table
    function recalcMasterSummary() {
        var totalDec = 0, totalRec = 0, totalBal = 0, totalTdsDec = 0, totalTdsRec = 0;

        // Add legacy rows
        <?php foreach ($oldPayments as $op) { ?>
            totalDec += <?php echo floatval($op['amount_decided']); ?>;
            totalRec += <?php echo floatval($op['payment_received']); ?>;
            totalBal += <?php echo floatval($op['balance']); ?>;
        <?php } ?>

        // Add editable rows
        $('#masterPaymentTable tbody tr.tr-editable').each(function() {
            var dec = parseFloat($(this).find('.field-decided').val()) || 0;
            var rec = parseFloat($(this).find('.field-received').val()) || 0;
            var bal = dec - rec;
            var tdDec = parseFloat($(this).find('.field-tds-dec').val()) || 0;
            var tdRec = parseFloat($(this).find('.field-tds-rec').val()) || 0;

            totalDec += dec;
            totalRec += rec;
            totalBal += bal;
            totalTdsDec += tdDec;
            totalTdsRec += tdRec;
        });

        // Format to INR
        function fmt(val) {
            return '₹ ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $('#kpiDecided, #footDecided').text(fmt(totalDec));
        $('#kpiReceived, #footReceived').text(fmt(totalRec));
        $('#kpiBalance, #footBalance').text(fmt(totalBal));
        $('#kpiTdsDec, #footTdsDec').text(fmt(totalTdsDec));
        $('#kpiTdsRec, #footTdsRec').text(fmt(totalTdsRec));
    }

    // Add New Row Action (dynamically appends beyond the initial rows)
    var dynamicRowCounter = 0;
    $('#btnAddNewRow').on('click', function() {
        dynamicRowCounter++;
        var rowCount = $('#masterPaymentTable tbody tr').length + 1;
        var newRowHtml = `
            <tr class="tr-editable" data-source="new" data-id="new_dyn_${dynamicRowCounter}">
                <td style="text-align: center; color: #64748b; font-weight: 600; font-family: var(--stmt-mono);" class="row-num">${rowCount}</td>
                <td>
                    <input type="text" class="stmt-input field-service" value="" placeholder="e.g. CA, SA-I, RC">
                </td>
                <td>
                    <input type="number" step="0.01" class="stmt-input num-field field-decided" value="0.00" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.01" class="stmt-input num-field field-received" value="0.00" placeholder="0.00">
                </td>
                <td>
                    <input type="text" class="stmt-input datepicker-stmt field-date" value="" placeholder="DD.MM.YYYY" autocomplete="off">
                </td>
                <td>
                    <input type="text" class="stmt-input field-instrument" value="" placeholder="NEFT / UTR / Cheque">
                </td>
                <td>
                    <input type="text" class="stmt-input num-field balance-field field-balance" value="0.00" readonly>
                </td>
                <td>
                    <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-dec" value="0.00" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.01" class="stmt-input num-field tds-field field-tds-rec" value="0.00" placeholder="0.00">
                </td>
                <td>
                    <div class="stmt-row-actions">
                        <button type="button" class="btn-row-action btn-row-save" title="Save this row">
                            <i class="fa fa-floppy-o"></i>
                        </button>
                        <button type="button" class="btn-row-action btn-row-delete" data-id="new_dyn_${dynamicRowCounter}" title="Delete row">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        $('#masterPaymentTable tbody').append(newRowHtml);
        initDatepickers();
        $('#masterPaymentTable tbody tr:last .field-service').focus();
    });

    // Save Single Row
    $(document).on('click', '.btn-row-save', function() {
        var $row = $(this).closest('tr');
        saveRowData($row);
    });

    function saveRowData($row) {
        var rowId = $row.attr('data-id');
        var serviceType = $.trim($row.find('.field-service').val());
        var decided = parseFloat($row.find('.field-decided').val()) || 0;
        var received = parseFloat($row.find('.field-received').val()) || 0;
        var tdsDec = parseFloat($row.find('.field-tds-dec').val()) || 0;

        if (emptyCheck(rowId) && emptyCheck(serviceType) && decided === 0 && received === 0 && tdsDec === 0) {
            showToast('Please enter service type or amount before saving.', 'info');
            return;
        }

        function emptyCheck(v) { return !v || v.indexOf('new_') === 0 || v.length === 0; }

        var data = {
            action: 'save_payment',
            customer_id: customerId,
            id: (String(rowId).indexOf('new_') === 0) ? '' : rowId,
            service_type: serviceType,
            amount_decided: decided,
            payment_received: received,
            payment_date: $row.find('.field-date').val(),
            instrument: $row.find('.field-instrument').val(),
            tds_decided: tdsDec,
            tds_received: $row.find('.field-tds-rec').val(),
            comment: ''
        };

        var $saveBtn = $row.find('.btn-row-save');
        $saveBtn.html('<i class="fa fa-spinner fa-spin"></i>');

        $.post('statementApi.php', data, function(res) {
            $saveBtn.html('<i class="fa fa-floppy-o"></i>');
            if (res.success) {
                $row.attr('data-id', res.id);
                $row.find('.btn-row-delete').attr('data-id', res.id);
                showToast('Payment entry saved successfully!', 'success');
            } else {
                showToast('Save failed: ' + (res.message || ''), 'error');
            }
        }, 'json').fail(function() {
            $saveBtn.html('<i class="fa fa-floppy-o"></i>');
            showToast('Server error while saving.', 'error');
        });
    }

    // Save All Entries
    $('#btnSaveAllRows').on('click', function() {
        var rows = [];
        $('#masterPaymentTable tbody tr.tr-editable').each(function() {
            var $r = $(this);
            var sType = $.trim($r.find('.field-service').val());
            var dAmt = parseFloat($r.find('.field-decided').val()) || 0;
            var rAmt = parseFloat($r.find('.field-received').val()) || 0;
            var tDec = parseFloat($r.find('.field-tds-dec').val()) || 0;
            var rowId = $r.attr('data-id');

            // Skip completely empty new placeholder rows
            if (String(rowId).indexOf('new_') === 0 && !sType && dAmt === 0 && rAmt === 0 && tDec === 0) {
                return;
            }

            rows.push({
                id: rowId,
                service_type: sType,
                amount_decided: dAmt,
                payment_received: rAmt,
                payment_date: $r.find('.field-date').val(),
                instrument: $r.find('.field-instrument').val(),
                tds_decided: tDec,
                tds_received: $r.find('.field-tds-rec').val(),
                comment: ''
            });
        });

        if (rows.length === 0) {
            showToast('No entries with data to save. Enter payment details first.', 'info');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving All...');

        $.post('statementApi.php', {
            action: 'bulk_save',
            customer_id: customerId,
            rows: JSON.stringify(rows)
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-floppy-o"></i> Save All Entries');
            if (res.success) {
                showToast('All statement entries saved successfully!', 'success');
                setTimeout(function() { location.reload(); }, 800);
            } else {
                showToast('Save failed: ' + (res.message || ''), 'error');
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-floppy-o"></i> Save All Entries');
            showToast('Server error during bulk save.', 'error');
        });
    });

    // Delete Row
    $(document).on('click', '.btn-row-delete', function() {
        var $row = $(this).closest('tr');
        var id = $(this).attr('data-id');

        if (String(id).indexOf('new_') === 0) {
            $row.fadeOut(200, function() { $(this).remove(); recalcMasterSummary(); });
            return;
        }

        if (!confirm('Are you sure you want to delete this payment entry?')) return;

        $.post('statementApi.php', { action: 'delete_payment', id: id }, function(res) {
            if (res.success) {
                $row.fadeOut(200, function() { $(this).remove(); recalcMasterSummary(); });
                showToast('Payment entry deleted.', 'success');
            } else {
                showToast('Delete failed: ' + (res.message || ''), 'error');
            }
        }, 'json');
    });

    // Bank Reconciliation Modal Handlers
    $('#openBankModalBtn').on('click', function() {
        $('#bankReconcileModal').fadeIn(200);
    });
    $('#closeBankModal, #cancelBankModal').on('click', function() {
        $('#bankReconcileModal').fadeOut(200);
    });
    $('#bankReconcileModal').on('click', function(e) {
        if (e.target === this) $(this).fadeOut(200);
    });

    $('#acknowledgeCheck').on('change', function() {
        $('#confirmReconcile').prop('disabled', !this.checked);
    });

    $('#confirmReconcile').on('click', function() {
        var formData = new FormData();
        formData.append('action', 'save_reconciliation');
        formData.append('customer_id', customerId);
        formData.append('acknowledgement_text', 'I acknowledge that all the payment entries match against the bank statement.');

        var fileInput = document.getElementById('bankStatementFile');
        if (fileInput.files.length > 0) {
            formData.append('bank_statement', fileInput.files[0]);
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Reconciling...');

        $.ajax({
            url: 'statementApi.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('Bank statement reconciled successfully!', 'success');
                    setTimeout(function() { location.reload(); }, 900);
                } else {
                    showToast('Error: ' + (res.message || ''), 'error');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Reconcile');
                }
            },
            error: function() {
                showToast('Server error.', 'error');
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Reconcile');
            }
        });
    });

    // Menu toggle support
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("active");
    });

    // Toast Notification Function
    function showToast(msg, type) {
        var bg = '#0d4d8b';
        var icon = 'fa-info-circle';
        if (type === 'success') { bg = '#059669'; icon = 'fa-check-circle'; }
        if (type === 'error') { bg = '#dc2626'; icon = 'fa-exclamation-triangle'; }
        if (type === 'info') { bg = '#475569'; icon = 'fa-info-circle'; }

        var $toast = $(`
            <div class="stmt-toast" style="background: ${bg};">
                <i class="fa ${icon}"></i>
                <span>${msg}</span>
            </div>
        `);
        $('body').append($toast);
        setTimeout(function() {
            $toast.fadeOut(300, function() { $(this).remove(); });
        }, 3200);
    }
});
</script>

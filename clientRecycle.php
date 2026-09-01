<div id="wrapper" class="active">
    <?php
    $pageIdentifier = "Recycle";
    include_once './header.php';
    include_once './dbConnect.php';
    include_once './include/customer_recycle_helper.php';
    date_default_timezone_set('Asia/Kolkata');

    aitfEnsureRecycleSchema($db);

    $clientList = aitfGetDeletedClients($db);
    $serviceList = aitfGetDeletedServices($db);

    $totalDeletedClients = count($clientList);
    $totalDeletedServices = count($serviceList);
    $totalTrashItems = $totalDeletedClients + $totalDeletedServices;

    $activeTab = isset($_GET['tab']) && $_GET['tab'] === 'services' ? 'services' : 'clients';

    function formatDeletedWhenText($dateStr) {
        if (empty($dateStr) || $dateStr === '0000-00-00 00:00:00') {
            return 'Previously Deleted';
        }
        $ts = strtotime($dateStr);
        if (!$ts) return 'Previously Deleted';
        if (date('Y-m-d') === date('Y-m-d', $ts)) {
            return 'Today at ' . date('h:i A', $ts);
        }
        return date('d M Y, h:i A', $ts);
    }
    ?>

    <div id="page-content-wrapper">
        <div class="page-content inset">
            <div class="container-fluid master-creator-container">

                <!-- Floating Toast Alert -->
                <div id="recycle-alert" class="recycle-toast" style="display: none;">
                    <i class="fa fa-check-circle toast-icon"></i>
                    <span id="recycle-alert-text">Action completed successfully.</span>
                </div>

                <!-- Hero Header matching masterCreator.php design system -->
                <div class="mc-hero-header">
                    <div class="mc-hero-left">
                        <div class="mc-hero-badge"><i class="fa fa-recycle"></i> SYSTEM RECYCLE BIN</div>
                        <h1 class="mc-hero-title">Recycle Bin</h1>
                        <p class="mc-hero-subtitle">Manage deleted clients and services. Restore them back to active workflows or delete permanently.</p>
                    </div>
                    <div class="mc-hero-right">
                        <div class="mc-hero-stats">
                            <div class="mc-stat-pill">
                                <span class="mc-stat-icon icon-purple"><i class="fa fa-archive"></i></span>
                                <div>
                                    <div class="mc-stat-num" id="stat-total-items"><?php echo $totalTrashItems; ?></div>
                                    <div class="mc-stat-lbl">Total Items</div>
                                </div>
                            </div>
                            <div class="mc-stat-pill">
                                <span class="mc-stat-icon icon-blue"><i class="fa fa-users"></i></span>
                                <div>
                                    <div class="mc-stat-num" id="stat-client-count"><?php echo $totalDeletedClients; ?></div>
                                    <div class="mc-stat-lbl">Deleted Clients</div>
                                </div>
                            </div>
                            <div class="mc-stat-pill">
                                <span class="mc-stat-icon icon-green"><i class="fa fa-cogs"></i></span>
                                <div>
                                    <div class="mc-stat-num" id="stat-service-count"><?php echo $totalDeletedServices; ?></div>
                                    <div class="mc-stat-lbl">Deleted Services</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="btnEmptyBin" class="mc-btn-empty-bin" <?php echo $totalTrashItems === 0 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                            <i class="fa fa-trash-o"></i> Empty Bin
                        </button>
                    </div>
                </div>

                <!-- Segmented Tabs & Search Bar -->
                <div class="mc-tab-toolbar">
                    <div class="mc-tab-bar">
                        <button type="button" class="mc-tab-btn <?php echo $activeTab === 'clients' ? 'active' : ''; ?>" data-target="#tab-clients">
                            <i class="fa fa-users"></i> Deleted Clients
                            <span class="mc-tab-count" id="tab-badge-clients"><?php echo $totalDeletedClients; ?></span>
                        </button>
                        <button type="button" class="mc-tab-btn <?php echo $activeTab === 'services' ? 'active' : ''; ?>" data-target="#tab-services">
                            <i class="fa fa-cogs"></i> Deleted Services
                            <span class="mc-tab-count" id="tab-badge-services"><?php echo $totalDeletedServices; ?></span>
                        </button>
                    </div>

                    <div class="mc-search-box">
                        <i class="fa fa-search mc-search-icon"></i>
                        <input type="text" id="recycleSearchInput" class="form-control mc-input-search" placeholder="Search by name...">
                    </div>
                </div>

                <!-- TAB 1: DELETED CLIENTS -->
                <div id="tab-clients" class="mc-tab-pane <?php echo $activeTab === 'clients' ? 'active' : ''; ?>">
                    <div class="mc-card">
                        <div class="mc-card-header">
                            <span class="mc-card-title"><i class="fa fa-users"></i> Deleted Clients</span>
                        </div>
                        <div class="mc-table-responsive">
                            <table class="table mc-table" id="table-deleted-clients">
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th style="width: 250px;">Deleted When</th>
                                        <th style="width: 120px; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($clientList)) { ?>
                                        <tr class="empty-row">
                                            <td colspan="3" class="mc-empty-cell">
                                                <div class="mc-empty-state">
                                                    <i class="fa fa-trash-o mc-empty-icon"></i>
                                                    <p class="mc-empty-title">Recycle Bin is Empty</p>
                                                    <p class="mc-empty-subtitle">No deleted clients found.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } else {
                                        foreach ($clientList as $client) {
                                            $clientId = (int)$client['id'];
                                            $clientName = htmlspecialchars($client['customer_name'] ?? 'Unnamed Client', ENT_QUOTES, 'UTF-8');
                                            $deletedText = formatDeletedWhenText($client['deleted_date'] ?? null);
                                    ?>
                                        <tr id="client-row-<?php echo $clientId; ?>" class="client-data-row" data-name="<?php echo strtolower($clientName); ?>">
                                            <td class="cell-client-name">
                                                <a href="customerAccount.php?customerid=<?php echo $clientId; ?>" class="mc-client-link" title="Open customer account">
                                                    <?php echo $clientName; ?>
                                                </a>
                                            </td>
                                            <td class="cell-deleted-date">
                                                <span class="mc-date-badge">
                                                    <i class="fa fa-clock-o"></i> <?php echo $deletedText; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <button type="button" class="mc-icon-btn mc-icon-restore restoreCustomer" data-id="<?php echo $clientId; ?>" data-name="<?php echo $clientName; ?>" title="Restore Client">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                                <button type="button" class="mc-icon-btn mc-icon-delete deleteCustomer" data-id="<?php echo $clientId; ?>" data-name="<?php echo $clientName; ?>" title="Permanently Delete Client">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: DELETED SERVICES -->
                <div id="tab-services" class="mc-tab-pane <?php echo $activeTab === 'services' ? 'active' : ''; ?>">
                    <div class="mc-card">
                        <div class="mc-card-header">
                            <span class="mc-card-title"><i class="fa fa-cogs"></i> Deleted Client Services</span>
                        </div>
                        <div class="mc-table-responsive">
                            <table class="table mc-table" id="table-deleted-services">
                                <thead>
                                    <tr>
                                        <th>Client & Service</th>
                                        <th style="width: 250px;">Deleted When</th>
                                        <th style="width: 120px; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($serviceList)) { ?>
                                        <tr class="empty-row">
                                            <td colspan="3" class="mc-empty-cell">
                                                <div class="mc-empty-state">
                                                    <i class="fa fa-cogs mc-empty-icon"></i>
                                                    <p class="mc-empty-title">No Deleted Services</p>
                                                    <p class="mc-empty-subtitle">Services deleted from customer account will appear here.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } else {
                                        foreach ($serviceList as $svc) {
                                            $serviceId = (int)$svc['service_id'];
                                            $serviceType = htmlspecialchars($svc['service_type'] ?? 'Service', ENT_QUOTES, 'UTF-8');
                                            $clientName = htmlspecialchars($svc['customer_name'] ?? 'Unnamed Client', ENT_QUOTES, 'UTF-8');
                                            $customerId = (int)($svc['customer_id'] ?? 0);
                                            $deletedText = formatDeletedWhenText($svc['deleted_date'] ?? null);
                                            $normType = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $serviceType));
                                    ?>
                                        <tr id="service-row-<?php echo $serviceId; ?>" class="service-data-row" data-name="<?php echo strtolower($clientName . ' ' . $serviceType); ?>">
                                            <td class="cell-client-name">
                                                <a href="customerAccount.php?customerid=<?php echo $customerId; ?>" class="mc-client-link" title="Open customer account">
                                                    <?php echo $clientName; ?>
                                                </a>
                                                <span class="mc-svc-badge mc-svc-<?php echo $normType; ?>">
                                                    <?php echo $serviceType; ?>
                                                </span>
                                            </td>
                                            <td class="cell-deleted-date">
                                                <span class="mc-date-badge">
                                                    <i class="fa fa-clock-o"></i> <?php echo $deletedText; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right; white-space: nowrap;">
                                                <button type="button" class="mc-icon-btn mc-icon-restore restoreService" data-id="<?php echo $serviceId; ?>" data-type="<?php echo $serviceType; ?>" data-client="<?php echo $clientName; ?>" title="Restore Service">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                                <button type="button" class="mc-icon-btn mc-icon-delete deleteServicePermanent" data-id="<?php echo $serviceId; ?>" data-type="<?php echo $serviceType; ?>" data-client="<?php echo $clientName; ?>" title="Permanently Delete Service">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- =======================================================================
     CONFIRMATION MODAL (SaaS Modern Clean UI)
     ======================================================================= -->
<div id="mc-modal-overlay" class="mc-modal-overlay" style="display: none;">
    <div class="mc-modal-box">
        <div class="mc-modal-icon-wrapper" id="mc-modal-icon">
            <i class="fa fa-question"></i>
        </div>
        <h3 class="mc-modal-title" id="mc-modal-title">Confirm Action</h3>
        <p class="mc-modal-message" id="mc-modal-message">Are you sure you want to proceed?</p>
        <div class="mc-modal-actions">
            <button type="button" class="btn mc-modal-btn mc-modal-btn-cancel" id="mc-modal-cancel-btn">Cancel</button>
            <button type="button" class="btn mc-modal-btn mc-modal-btn-confirm" id="mc-modal-confirm-btn">Confirm</button>
        </div>
    </div>
</div>

<!-- =======================================================================
     STYLES — MATCHING masterCreator.php DESIGN SYSTEM
     ======================================================================= -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    body, #wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        background-color: #f8fafc !important;
        color: #0f172a !important;
        -webkit-font-smoothing: antialiased;
    }

    .master-creator-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 20px 20px 60px 20px;
    }

    /* Floating Toast */
    .recycle-toast {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 99999;
        min-width: 290px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
        padding: 12px 18px;
        border: none;
        background: #059669;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInToast 0.25s ease;
    }
    @keyframes slideInToast {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Hero Header - Exact Match with masterCreator.php */
    .mc-hero-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #eff6ff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
    }

    .mc-hero-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #0d4d8b 0%, #2563eb 50%, #38bdf8 100%);
    }

    .mc-hero-left {
        max-width: 480px;
    }

    .mc-hero-badge {
        font-size: 11px;
        font-weight: 800;
        color: #0d4d8b;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mc-hero-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 5px 0;
        letter-spacing: -0.02em;
    }

    .mc-hero-subtitle {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }

    .mc-hero-right {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .mc-hero-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mc-stat-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .mc-stat-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .mc-stat-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .mc-stat-icon.icon-blue { background: #eff6ff; color: #2563eb; }
    .mc-stat-icon.icon-green { background: #ecfdf5; color: #059669; }
    .mc-stat-icon.icon-purple { background: #f5f3ff; color: #7c3aed; }

    .mc-stat-num {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .mc-stat-lbl {
        font-size: 9.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1;
        margin-top: 3px;
    }

    /* Empty Bin Button */
    .mc-btn-empty-bin {
        background: #ffffff;
        border: 1px solid #fecaca;
        color: #dc2626;
        border-radius: 9px;
        padding: 0 16px;
        height: 42px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.08);
        transition: all 0.18s ease;
    }

    .mc-btn-empty-bin:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    /* Tab Toolbar */
    .mc-tab-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .mc-tab-bar {
        display: flex;
        gap: 8px;
    }

    .mc-tab-btn {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .mc-tab-btn:hover {
        background: #f1f5f9;
        color: #0d4d8b;
        border-color: #cbd5e1;
    }

    .mc-tab-btn.active {
        background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
        color: #ffffff;
        border-color: #0d4d8b;
        box-shadow: 0 4px 12px rgba(13, 77, 139, 0.2);
    }

    .mc-tab-count {
        background: rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 2px 7px;
        font-size: 11px;
        font-weight: 800;
    }

    .mc-tab-btn.active .mc-tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Search Box */
    .mc-search-box {
        position: relative;
        min-width: 250px;
    }

    .mc-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 12.5px;
    }

    .mc-input-search {
        padding-left: 34px !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        height: 38px !important;
        color: #0f172a !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
        transition: all 0.15s ease !important;
    }

    .mc-input-search:focus {
        border-color: #0d4d8b !important;
        box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.15) !important;
        outline: none !important;
    }

    /* Cards */
    .mc-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .mc-card-header {
        background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
        padding: 12px 20px;
        display: flex;
        align-items: center;
    }

    .mc-card-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Modern Minimalist Table */
    .mc-table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .mc-table {
        width: 100%;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .mc-table thead th {
        background: #f8fafc !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 13px 20px !important;
        vertical-align: middle !important;
        border-top: none !important;
    }

    .mc-table tbody td {
        padding: 15px 20px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 13.5px !important;
        color: #334155 !important;
        transition: background 0.12s ease !important;
    }

    .mc-table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .mc-table tbody tr:hover td {
        background: #f8fafc !important;
    }

    .cell-client-name {
        font-weight: 600;
    }

    .mc-client-link {
        color: #0f172a !important;
        text-decoration: none !important;
        font-weight: 600 !important;
        transition: color 0.15s ease !important;
    }

    .mc-client-link:hover {
        color: #0d4d8b !important;
        text-decoration: underline !important;
    }

    .mc-svc-badge {
        display: inline-block;
        padding: 2.5px 8px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        margin-left: 8px;
    }

    .mc-svc-ca { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .mc-svc-sai, .mc-svc-sa1 { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
    .mc-svc-saii, .mc-svc-sa2 { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .mc-svc-rc { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .mc-svc-cr { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .mc-svc-ptr { background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; }
    .mc-svc-sp { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; }
    .mc-svc-ta { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }

    .mc-date-badge {
        color: #64748b;
        font-size: 12.5px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mc-date-badge i {
        color: #94a3b8;
    }

    /* 2 Sleek Action Icons */
    .mc-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.15s ease;
        margin-left: 6px;
    }

    .mc-icon-restore {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }

    .mc-icon-restore:hover {
        background: #059669;
        color: #ffffff;
        border-color: #059669;
        box-shadow: 0 3px 8px rgba(5, 150, 105, 0.25);
    }

    .mc-icon-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .mc-icon-delete:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
    }

    /* Empty Cell */
    .mc-empty-cell {
        padding: 55px 20px !important;
        text-align: center !important;
    }

    .mc-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .mc-empty-icon {
        font-size: 36px;
        color: #cbd5e1;
        margin-bottom: 8px;
    }

    .mc-empty-title {
        font-size: 15px;
        font-weight: 700;
        color: #475569;
        margin: 0 0 3px 0;
    }

    .mc-empty-subtitle {
        font-size: 12.5px;
        color: #94a3b8;
        margin: 0;
    }

    /* Tab Panes */
    .mc-tab-pane {
        display: none;
    }
    .mc-tab-pane.active {
        display: block;
        animation: fadeInTab 0.15s ease;
    }
    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Modal Overlay */
    .mc-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .mc-modal-box {
        background: #ffffff;
        border-radius: 14px;
        width: 100%;
        max-width: 410px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.22);
        border: 1px solid #e2e8f0;
        animation: scaleModalIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes scaleModalIn {
        from { transform: scale(0.94); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .mc-modal-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin: 0 auto 14px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .mc-modal-icon-wrapper.restore-type {
        background: #ecfdf5;
        color: #059669;
    }

    .mc-modal-icon-wrapper.delete-type {
        background: #fef2f2;
        color: #dc2626;
    }

    .mc-modal-title {
        font-size: 16.5px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px 0;
    }

    .mc-modal-message {
        font-size: 13.5px;
        color: #64748b;
        margin: 0 0 20px 0;
        line-height: 1.5;
    }

    .mc-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .mc-modal-btn {
        border-radius: 8px !important;
        padding: 8px 18px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        min-width: 96px;
        transition: all 0.15s ease !important;
    }

    .mc-modal-btn-confirm.confirm-restore {
        background: #059669 !important;
        color: #ffffff !important;
        border: none !important;
    }

    .mc-modal-btn-confirm.confirm-restore:hover {
        background: #047857 !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3) !important;
    }

    .mc-modal-btn-confirm.confirm-delete {
        background: #dc2626 !important;
        color: #ffffff !important;
        border: none !important;
    }

    .mc-modal-btn-confirm.confirm-delete:hover {
        background: #b91c1c !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
    }

    .mc-modal-btn-cancel {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }

    .mc-modal-btn-cancel:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }
</style>

<!-- =======================================================================
     JAVASCRIPT & AJAX
     ======================================================================= -->
<script type="text/javascript">
$(document).ready(function () {

    // Tab switcher
    $('.mc-tab-btn').click(function () {
        var target = $(this).attr('data-target');
        $('.mc-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.mc-tab-pane').removeClass('active');
        $(target).addClass('active');

        if (history.pushState) {
            var tabParam = target === '#tab-services' ? 'services' : 'clients';
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabParam;
            window.history.pushState({path: newUrl}, '', newUrl);
        }
    });

    // Search filter across rows
    $('#recycleSearchInput').on('keyup', function () {
        var query = $(this).val().toLowerCase().trim();
        $('.mc-tab-pane.active tbody tr:not(.empty-row)').each(function () {
            var name = $(this).attr('data-name') || '';
            if (query === '' || name.indexOf(query) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Toast alert
    function showToast(message, isError) {
        var $toast = $('#recycle-alert');
        $toast.css('background', isError ? '#dc2626' : '#059669');
        $('#recycle-alert-text').text(message);
        $toast.stop(true, true).fadeIn(150);
        setTimeout(function () {
            $toast.fadeOut(250);
        }, 3000);
    }

    // Modal Control
    var modalCallback = null;

    function openModal(options) {
        $('#mc-modal-title').text(options.title);
        $('#mc-modal-message').html(options.message);

        var $icon = $('#mc-modal-icon');
        $icon.removeClass('restore-type delete-type').addClass(options.iconType);
        $icon.html('<i class="fa ' + options.icon + '"></i>');

        var $confirmBtn = $('#mc-modal-confirm-btn');
        $confirmBtn.removeClass('confirm-restore confirm-delete').addClass(options.confirmClass);
        $confirmBtn.text(options.confirmText);

        modalCallback = options.onConfirm;
        $('#mc-modal-overlay').fadeIn(130);
    }

    $('#mc-modal-cancel-btn, #mc-modal-overlay').click(function (e) {
        if (e.target === this) {
            $('#mc-modal-overlay').fadeOut(100);
            modalCallback = null;
        }
    });

    $('#mc-modal-confirm-btn').click(function () {
        $('#mc-modal-overlay').fadeOut(100);
        if (typeof modalCallback === 'function') {
            modalCallback();
        }
    });

    function updateCounts(type, delta) {
        var $total = $('#stat-total-items');
        var currentTotal = parseInt($total.text()) || 0;
        var newTotal = Math.max(0, currentTotal + delta);
        $total.text(newTotal);

        if (type === 'client') {
            var $c = $('#stat-client-count');
            var $b = $('#tab-badge-clients');
            var val = Math.max(0, (parseInt($c.text()) || 0) + delta);
            $c.text(val);
            $b.text(val);
        } else if (type === 'service') {
            var $s = $('#stat-service-count');
            var $b = $('#tab-badge-services');
            var val = Math.max(0, (parseInt($s.text()) || 0) + delta);
            $s.text(val);
            $b.text(val);
        }

        if (newTotal === 0) {
            $('#btnEmptyBin').prop('disabled', true).css({ opacity: 0.5, cursor: 'not-allowed' });
        }
    }

    function checkEmptyTable(tableId, title, subtitle) {
        var visibleRows = $(tableId + ' tbody tr:not(.empty-row)').length;
        if (visibleRows === 0) {
            $(tableId + ' tbody').html('<tr class="empty-row"><td colspan="3" class="mc-empty-cell"><div class="mc-empty-state"><i class="fa fa-trash-o mc-empty-icon"></i><p class="mc-empty-title">' + title + '</p><p class="mc-empty-subtitle">' + subtitle + '</p></div></td></tr>');
        }
    }

    // Restore Client
    $(document).on('click', '.restoreCustomer', function () {
        var id = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        openModal({
            title: 'Restore Client',
            message: 'Are you sure you want to restore <strong>' + name + '</strong> to the active client list?',
            icon: 'fa-undo',
            iconType: 'restore-type',
            confirmText: 'Restore',
            confirmClass: 'confirm-restore',
            onConfirm: function () {
                $.ajax({
                    url: 'restoreCustomer.php',
                    type: 'POST',
                    data: { customerid: id, is_ajax: 1 },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            showToast(name + ' restored');
                            $('#client-row-' + id).fadeOut(200, function () {
                                $(this).remove();
                                updateCounts('client', -1);
                                checkEmptyTable('#table-deleted-clients', 'Recycle Bin is Empty', 'No deleted clients found.');
                            });
                        } else {
                            showToast('Failed to restore client', true);
                        }
                    },
                    error: function () {
                        location.reload();
                    }
                });
            }
        });
    });

    // Delete Client Permanently
    $(document).on('click', '.deleteCustomer', function () {
        var id = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        openModal({
            title: 'Delete Client Permanently',
            message: 'Are you sure you want to <strong>permanently delete</strong> <strong>' + name + '</strong>?<br><span style="color:#dc2626; font-size:12px;">All client data, audits, and certificates will be removed. This cannot be undone!</span>',
            icon: 'fa-trash-o',
            iconType: 'delete-type',
            confirmText: 'Delete Permanently',
            confirmClass: 'confirm-delete',
            onConfirm: function () {
                $.ajax({
                    url: 'restoreCustomer.php',
                    type: 'POST',
                    data: { customerDlt: id, is_ajax: 1 },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            showToast(name + ' permanently deleted');
                            $('#client-row-' + id).fadeOut(200, function () {
                                $(this).remove();
                                updateCounts('client', -1);
                                checkEmptyTable('#table-deleted-clients', 'Recycle Bin is Empty', 'No deleted clients found.');
                            });
                        } else {
                            showToast('Failed to delete client', true);
                        }
                    },
                    error: function () {
                        location.reload();
                    }
                });
            }
        });
    });

    // Restore Service
    $(document).on('click', '.restoreService', function () {
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
        var client = $(this).attr('data-client');
        openModal({
            title: 'Restore Service',
            message: 'Are you sure you want to restore <strong>' + type + '</strong> service for <strong>' + client + '</strong>?',
            icon: 'fa-undo',
            iconType: 'restore-type',
            confirmText: 'Restore',
            confirmClass: 'confirm-restore',
            onConfirm: function () {
                $.ajax({
                    url: 'restoreCustomer.php',
                    type: 'POST',
                    data: { restoreServiceId: id, is_ajax: 1 },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            showToast(type + ' service restored for ' + client);
                            $('#service-row-' + id).fadeOut(200, function () {
                                $(this).remove();
                                updateCounts('service', -1);
                                checkEmptyTable('#table-deleted-services', 'No Deleted Services', 'Services deleted from customer account will appear here.');
                            });
                        } else {
                            showToast('Failed to restore service', true);
                        }
                    },
                    error: function () {
                        location.reload();
                    }
                });
            }
        });
    });

    // Delete Service Permanently
    $(document).on('click', '.deleteServicePermanent', function () {
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
        var client = $(this).attr('data-client');
        openModal({
            title: 'Delete Service Permanently',
            message: 'Are you sure you want to <strong>permanently delete</strong> <strong>' + type + '</strong> service for <strong>' + client + '</strong>?<br><span style="color:#dc2626; font-size:12px;">All audit stages, notifications, and certificates for this service will be deleted permanently!</span>',
            icon: 'fa-trash-o',
            iconType: 'delete-type',
            confirmText: 'Delete Permanently',
            confirmClass: 'confirm-delete',
            onConfirm: function () {
                $.ajax({
                    url: 'restoreCustomer.php',
                    type: 'POST',
                    data: { deleteServicePermanent: id, is_ajax: 1 },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            showToast(type + ' service permanently deleted');
                            $('#service-row-' + id).fadeOut(200, function () {
                                $(this).remove();
                                updateCounts('service', -1);
                                checkEmptyTable('#table-deleted-services', 'No Deleted Services', 'Services deleted from customer account will appear here.');
                            });
                        } else {
                            showToast('Failed to delete service', true);
                        }
                    },
                    error: function () {
                        location.reload();
                    }
                });
            }
        });
    });

    // Empty Bin
    $('#btnEmptyBin').click(function () {
        openModal({
            title: 'Empty Recycle Bin',
            message: 'Are you sure you want to <strong>permanently delete all items</strong> in the Recycle Bin?<br><span style="color:#dc2626; font-size:12px;">All deleted clients and services will be permanently removed. This action cannot be undone!</span>',
            icon: 'fa-trash-o',
            iconType: 'delete-type',
            confirmText: 'Empty Bin',
            confirmClass: 'confirm-delete',
            onConfirm: function () {
                $.ajax({
                    url: 'restoreCustomer.php',
                    type: 'POST',
                    data: { action: 'empty_bin', is_ajax: 1 },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            showToast('Recycle Bin has been emptied');
                            $('.client-data-row, .service-data-row').fadeOut(250, function () {
                                $(this).remove();
                                $('#stat-total-items').text('0');
                                $('#stat-client-count').text('0');
                                $('#stat-service-count').text('0');
                                $('#tab-badge-clients').text('0');
                                $('#tab-badge-services').text('0');
                                checkEmptyTable('#table-deleted-clients', 'Recycle Bin is Empty', 'No deleted clients found.');
                                checkEmptyTable('#table-deleted-services', 'No Deleted Services', 'Services deleted from customer account will appear here.');
                                $('#btnEmptyBin').prop('disabled', true).css({ opacity: 0.5, cursor: 'not-allowed' });
                            });
                        } else {
                            showToast('Failed to empty bin', true);
                        }
                    },
                    error: function () {
                        location.reload();
                    }
                });
            }
        });
    });

});
</script>

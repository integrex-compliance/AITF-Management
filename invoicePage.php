<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php
   
    $pageIdentifier = "Invoices";
    include 'header.php';
    include 'dbConnect.php';

    $proformaInvoice = "SELECT id.invoice_id, customer_id, profarma_prefix, invoice_no, invoice_date, state, state_code, net_price, igst, cgst, sgst, tax_type, final_amount, invoice_type, pro_inv_sent_count, id.invoice_status, ci.customer_name, id, gstin_no FROM invoice_details id left join customer_info ci on id.customer_id = ci.id where id.invoice_type = 'proforma' AND id.is_deleted = 'No' order by invoice_date desc, invoice_no DESC";
//    $proformaInvoice = "SELECT * FROM invoice_details WHERE invoice_type = 'proforma'";
    $statement = $db->prepare($proformaInvoice);
    $statement->execute();
    $proformaDetails = $statement->fetchAll();

    $finalInvoice = "SELECT id.invoice_id, customer_id, final_prefix, final_invoice_no, invoice_date, state, state_code, net_price, igst, cgst, sgst, tax_type, final_amount, invoice_type, final_inv_sent_count, id.invoice_status, ci.customer_name, id, gstin_no FROM invoice_details id left join customer_info ci on id.customer_id = ci.id where id.invoice_type = 'final' AND id.is_deleted = 'No' order by invoice_date desc, final_invoice_no DESC";
//    $finalInvoice = "SELECT * FROM invoice_details WHERE invoice_type = 'final'";
    $statement = $db->prepare($finalInvoice);
    $statement->execute();
    $finalDetails = $statement->fetchAll();

    $firmDetails = "SELECT * FROM consulting_firm";
    $statement = $db->prepare($firmDetails);
    $statement->execute();
    $firmInfo = $statement->fetch();

    // Batch-load invoice services to avoid per-row queries
    $proformaSvcMap = array();
    if (!empty($proformaDetails)) {
        $proformaIds = array_column($proformaDetails, 'invoice_id');
        $placeholders = implode(',', array_fill(0, count($proformaIds), '?'));
        $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
        $stmtSvc = $db->prepare($svcSql);
        $stmtSvc->execute($proformaIds);
        foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $proformaSvcMap[$row['invoice_ids']][] = $row['service_desc'];
        }
    }

    $finalSvcMap = array();
    if (!empty($finalDetails)) {
        $finalIds = array_column($finalDetails, 'invoice_id');
        $placeholders = implode(',', array_fill(0, count($finalIds), '?'));
        $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
        $stmtSvc = $db->prepare($svcSql);
        $stmtSvc->execute($finalIds);
        foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $finalSvcMap[$row['invoice_ids']][] = $row['service_desc'];
        }
    }

    //query
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        #page-content-wrapper,
        #page-content-wrapper * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Keep FontAwesome glyphs working; the wildcard above otherwise forces Inter on <i> icons */
        #page-content-wrapper .fa {
            font-family: 'FontAwesome' !important;
        }

        /* Datepicker must float above sticky table headers/cards (jQuery UI sets a low inline z-index) */
        .ui-datepicker {
            z-index: 100000 !important;
        }

        /* ===== Modern Table styling ===== */
        .table-responsive {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef1f6;
            margin-bottom: 24px;
            background: #ffffff;
        }
        .invoice-table-card {
            background: #fff;
            border: 1px solid #eef1f6;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .table {
            margin-bottom: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0;
            border: 0 !important;
        }
        .table small { font-size: 13.5px !important; font-weight: 500; color: #334155; }
        .table tbody tr {
            transition: background 0.18s ease, box-shadow 0.18s ease;
        }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #fcfdff !important; }
        .table tbody tr:hover {
            background-color: #f1f5f9 !important;
        }
        .customerAccount { cursor: pointer; }

        #table_proforma_invoice thead th, #table_final_invoice thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border: 0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 15px 14px !important;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        #table_proforma_invoice thead th a, #table_final_invoice thead th a {
            color: #0f172a !important;
            text-decoration: none !important;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }
        .table tbody td {
            padding: 14px 14px !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
        }
        .table tbody tr:last-child td { border-bottom: 0 !important; }
        
        /* Modern Status: received = green, not received = red */
        .payment-rcv td, .payment-rcv small { color: #047857 !important; font-weight: 600; }
        .payment-not-rcv td, .payment-not-rcv small { color: #be123c !important; font-weight: 600; }
        .payment-rcv td:first-child { box-shadow: inset 4px 0 0 #10b981; }
        .payment-not-rcv td:first-child { box-shadow: inset 4px 0 0 #f43f5e; }
        .payment-rcv td:nth-child(10) small { color: #047857 !important; font-weight: 800; }
        .payment-not-rcv td:nth-child(10) small { color: #be123c !important; font-weight: 800; }
        
        /* Action buttons (modern icon chips) */
        .table td a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background-color: #f1f5f9;
            color: #64748b !important;
            margin: 0 2px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none !important;
        }
        .table td a i { font-size: 13px; }
        .table td a:hover {
            background-color: #e2e8f0;
            color: #0f172a !important;
            transform: translateY(-1px);
        }
        .table td a.finalInvoice:hover {
            background-color: #dbeafe;
            color: #2563eb !important;
        }
        .table td a.editInvoice:hover, .table td a.editFinalInvoice:hover {
            background-color: #fef9c3;
            color: #ca8a04 !important;
        }
        .table td a.viewInvoice:hover, .table td a.viewFinalInvoice:hover {
            background-color: #dcfce7;
            color: #16a34a !important;
        }
        .table td a.mailProformaInvoice:hover, .table td a.mailInvoice:hover {
            background-color: #ede9fe;
            color: #7c3aed !important;
        }
        .table td a.deleteInvoice:hover {
            background-color: #fee2e2;
            color: #dc2626 !important;
        }
        .custom-float-label-control {
            margin-bottom: 0em;
        }

        #loading-div-background{
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.5);
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        #loading-div{
            width: 300px;
            height: 150px;
            background-color: #fff;
            color:#1468b3;
            border: 5px solid #1468b3;
            text-align: center;
            color: #202020;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            opacity: 1.0;
            behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
        }

        .invoice-section-title { display: none; }

        .invoice-section-divider {
            margin: 0;
            padding: 0;
            border: none;
        }

        /* ===== Modern Toolbar (Tabs + Filters on one line) ===== */
        .invoice-toolbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            background: #ffffff;
            border: 1px solid #eef1f6;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            padding: 14px 18px;
            margin: 0 0 22px;
        }
        .invoice-tabs-nav {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px;
            background: #f1f5f9;
            border-radius: 999px;
        }
        .invoice-tab {
            position: relative;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.1px;
            padding: 9px 26px;
            border-radius: 999px;
            cursor: pointer;
            white-space: nowrap;
            outline: none;
            transition: color 0.28s ease, background 0.28s ease, box-shadow 0.28s ease, transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .invoice-tab:hover { color: #0d4d8b; }
        .invoice-tab.active {
            background: linear-gradient(135deg, #0d4d8b 0%, #1468b3 100%);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(13, 77, 139, 0.30);
            transform: translateY(-1px);
        }
        .invoice-tab.active:hover { color: #ffffff; }

        .invoice-filter-bar { padding-top: 14px; }
        .filter-set { display: none; flex: 1 1 auto; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
        .filter-set.active { display: flex; animation: invoiceFadeIn 0.3s ease; }

        /* Only the active tab's panel/table should be visible */
        .invoice-tab-panel { display: none; }
        .invoice-tab-panel.active { display: block; }

        .invoice-filter-item, .filter-field {
            display: flex;
            flex-direction: column;
            min-width: 180px;
            flex: 1;
        }
        .invoice-filter-item label, .filter-field label {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .invoice-filter-item input, .filter-field input {
            width: 100%;
            height: 42px;
            padding: 8px 14px;
            border: 1px solid #d8dee9;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
            color: #0f172a;
            background-color: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .invoice-filter-item input::placeholder, .filter-field input::placeholder { color: #94a3b8; }
        .invoice-filter-item input:focus, .filter-field input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 4px rgba(13, 77, 139, 0.12);
        }
        .filter-field.btn-col { min-width: auto; flex: 0 0 auto; }

        /* Modern Download / Export Button */
        .btn-report-export, .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 22px;
            box-sizing: border-box;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.2px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.4, 0,0.2,1), box-shadow 0.2s ease, background 0.2s ease;
            text-transform: none;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .btn-solid-primary, .btn-download {
            background: linear-gradient(135deg, #0d4d8b 0%, #1468b3 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.25);
        }
        .btn-solid-primary:hover, .btn-download:hover {
            box-shadow: 0 8px 20px rgba(13, 77, 139, 0.32);
            transform: translateY(-1px);
        }
        .btn-solid-primary:active, .btn-download:active { transform: translateY(0); }

        /* 2. Outlined Premium - Notion Style */
        .btn-outlined-premium {
            background-color: #ffffff;
            color: #0d4d8b;
            border: 1px solid #bfd2e6;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-outlined-premium:hover {
            border-color: #0d4d8b;
            background-color: #f8fbff;
            color: #0d4d8b;
        }
        .btn-outlined-premium:active { background-color: #edf3ff; }

        /* 3. Soft Filled - Slack/Atlassian Style */
        .btn-soft-filled {
            background-color: #eaf3ff;
            color: #0d4d8b;
            box-shadow: none;
        }
        .btn-soft-filled:hover { background-color: #d6e8ff; color: #093967; }
        .btn-soft-filled:active { background-color: #c2ddff; }

        #table_proforma_invoice_filter,
        #table_final_invoice_filter {
            display: none;
        }

        .invoice-mail-confirm {
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f8fbff;
            border: 1px solid #d6e4f0;
            border-radius: 6px;
            color: #0d4d8b;
        }

        .invoice-mail-confirm strong {
            color: #083a69;
        }

        @media (max-width: 992px) {
            .invoice-toolbar { flex-direction: column; align-items: stretch; }
            .invoice-tabs-nav { width: 100%; justify-content: center; }
            .filter-set { width: 100%; }
        }
        @media (max-width: 768px) {
            .filter-set { flex-direction: column; }
            .invoice-filter-item, .filter-field { min-width: 100%; }
            .filter-field.btn-col { align-self: stretch; }
            .btn-download, .btn-report-export { width: 100%; }
            .invoice-tab { padding: 9px 18px; font-size: 13px; }
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  invoicePage.php
           Palette mirrors homePage.php: deep charcoal surfaces, soft slate text,
           jewel-toned accents. Toggle handled globally (head.php + js/app.js).
           ===================================================================== */
        html.theme-dark {
            --hpd-bg:        #0a0e14;
            --hpd-surface:   #111722;
            --hpd-surface-2: #161d2b;
            --hpd-border:    #232c3d;
            --hpd-border-2:  #2e3a4f;
            --hpd-text:      #dbe3f0;
            --hpd-text-dim:  #93a1b8;
            --hpd-text-faint:#6b7890;
            --hpd-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper {
            background: var(--hpd-bg);
            color: var(--hpd-text);
        }
        html.theme-dark .page-content.inset { background: var(--hpd-bg); }

        /* ---- Tables / cards ---- */
        html.theme-dark .table-responsive,
        html.theme-dark .invoice-table-card {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .table small { color: var(--hpd-text-dim); }
        html.theme-dark #table_proforma_invoice thead th,
        html.theme-dark #table_final_invoice thead th {
            background: linear-gradient(180deg, #161d2b 0%, #121926 100%) !important;
            border-bottom: 1px solid var(--hpd-border-2) !important;
        }
        html.theme-dark #table_proforma_invoice thead th a,
        html.theme-dark #table_final_invoice thead th a { color: #aeb9cc !important; }
        html.theme-dark .table tbody td {
            border-bottom: 1px solid var(--hpd-border) !important;
            color: var(--hpd-text);
        }
        html.theme-dark .table tbody tr:hover { background-color: var(--hpd-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255, 255, 255, 0.018) !important; }

        /* Payment status — luminous on dark */
        html.theme-dark .payment-rcv td,
        html.theme-dark .payment-rcv small,
        html.theme-dark .payment-rcv td:nth-child(10) small { color: #6ee7b7 !important; }
        html.theme-dark .payment-not-rcv td,
        html.theme-dark .payment-not-rcv small,
        html.theme-dark .payment-not-rcv td:nth-child(10) small { color: #fca5a5 !important; }
        html.theme-dark .payment-rcv td:first-child { box-shadow: inset 4px 0 0 #10b981; }
        html.theme-dark .payment-not-rcv td:first-child { box-shadow: inset 4px 0 0 #f43f5e; }

        /* Action icon chips */
        html.theme-dark .table td a {
            background-color: var(--hpd-surface-2);
            color: var(--hpd-text-dim) !important;
            border-color: var(--hpd-border);
        }
        html.theme-dark .table td a:hover { background-color: #1f2a3d; color: #ffffff !important; }
        html.theme-dark .table td a.finalInvoice:hover { background-color: rgba(37,99,235,0.22); color: #93c5fd !important; }
        html.theme-dark .table td a.editInvoice:hover,
        html.theme-dark .table td a.editFinalInvoice:hover { background-color: rgba(202,138,4,0.22); color: #fde68a !important; }
        html.theme-dark .table td a.viewInvoice:hover,
        html.theme-dark .table td a.viewFinalInvoice:hover { background-color: rgba(22,163,74,0.22); color: #86efac !important; }
        html.theme-dark .table td a.mailProformaInvoice:hover,
        html.theme-dark .table td a.mailInvoice:hover { background-color: rgba(124,58,237,0.22); color: #c4b5fd !important; }
        html.theme-dark .table td a.deleteInvoice:hover { background-color: rgba(220,38,38,0.22); color: #fca5a5 !important; }

        /* ---- Toolbar / tabs ---- */
        html.theme-dark .invoice-toolbar {
            background: var(--hpd-surface);
            border-color: var(--hpd-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .invoice-tabs-nav { background: #0e131c; }
        html.theme-dark .invoice-tab { color: var(--hpd-text-dim); }
        html.theme-dark .invoice-tab:hover { color: #7fb6ef; }
        html.theme-dark .invoice-tab.active {
            background: linear-gradient(135deg, #1d63b0 0%, #2f81d6 100%);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(20, 104, 179, 0.4);
        }

        /* ---- Filters ---- */
        html.theme-dark .invoice-filter-item label,
        html.theme-dark .filter-field label { color: var(--hpd-text-dim); }
        html.theme-dark .invoice-filter-item input,
        html.theme-dark .filter-field input {
            background-color: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .invoice-filter-item input::placeholder,
        html.theme-dark .filter-field input::placeholder { color: var(--hpd-text-faint); }
        html.theme-dark .invoice-filter-item input:focus,
        html.theme-dark .filter-field input:focus {
            border-color: var(--hpd-accent);
            box-shadow: 0 0 0 4px rgba(74, 158, 255, 0.18);
        }

        /* Buttons */
        html.theme-dark .btn-solid-primary,
        html.theme-dark .btn-download {
            background: linear-gradient(135deg, #1d63b0 0%, #2f81d6 100%);
            box-shadow: 0 4px 14px rgba(20, 104, 179, 0.4);
            color: #ffffff;
        }
        html.theme-dark .btn-outlined-premium {
            background-color: var(--hpd-surface-2);
            color: #7fb6ef;
            border-color: var(--hpd-border-2);
        }
        html.theme-dark .btn-outlined-premium:hover { border-color: var(--hpd-accent); background-color: #1a2334; color: #9ecbf5; }
        html.theme-dark .btn-soft-filled {
            background-color: rgba(74,158,255,0.14);
            color: #7fb6ef;
        }
        html.theme-dark .btn-soft-filled:hover { background-color: rgba(74,158,255,0.22); color: #9ecbf5; }

        /* ---- Modals ---- */
        html.theme-dark .modal-content {
            background: var(--hpd-surface);
            border: 1px solid var(--hpd-border-2);
            color: var(--hpd-text);
        }
        html.theme-dark .modal-header,
        html.theme-dark .modal-footer { border-color: var(--hpd-border); }
        html.theme-dark .modal-title { color: var(--hpd-text); }
        html.theme-dark .invoice-mail-confirm {
            background: var(--hpd-surface-2);
            border-color: var(--hpd-border-2);
            color: #9ecbf5;
        }
        html.theme-dark .invoice-mail-confirm strong { color: #bcd9f7; }

        /* ---- Loading overlay / spinner ---- */
        html.theme-dark #loading-div-background { background-color: rgba(4, 8, 14, 0.85) !important; }
        html.theme-dark #loading-div,
        html.theme-dark .spinner-container {
            background-color: var(--hpd-surface) !important;
            border-color: var(--hpd-accent);
            color: var(--hpd-text);
        }
        html.theme-dark .spinner-circle { border-color: #232c3d; border-top-color: var(--hpd-accent); }
        html.theme-dark .spinner-text { color: var(--hpd-text-dim); }
    </style>

    <div id="page-content-wrapper">

        <div id="loading-div-background">
            <div id="loading-div" class="modern-spinner">
                <div class="spinner-container">
                    <div class="spinner-circle"></div>
                    <div class="spinner-text">Loading...</div>
                </div>
            </div>
        </div>
        <style>
            #loading-div-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            
            .modern-spinner {
                text-align: center;
            }
            
            .spinner-container {
                display: inline-block;
                padding: 30px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            
            .spinner-circle {
                width: 60px;
                height: 60px;
                margin: 0 auto 20px;
                border: 5px solid #f0f0f0;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                animation: spin 1.5s linear infinite;
            }
            
            .spinner-text {
                color: #2c3e50;
                font-size: 16px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                animation: pulse 1.5s infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            @keyframes pulse {
                0% { opacity: 0.6; }
                50% { opacity: 1; }
                100% { opacity: 0.6; }
            }
        </style>

        <div class="modal fade" id="invoice-msg-sent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Send Proforma Invoice</h4>
                    </div>
                    <form class="customeraddform" id="customeraddform" action="invoicemailsend.php" method="post">
                        <div class="modal-body">
                            <div class="invoice-mail-confirm">
                                Do you want to send this invoice to the client via email?
                                <br>
                                <strong>Note:</strong> A copy of the email and document will also be shared at <strong>certification@aitf.co.in</strong>.
                            </div>
                        </div>
                        <input type="hidden" class="invoiceFirmId" name="invoiceFirmId" value="">
                        <input type="hidden" class="invoiceCustomerId" name="invoiceCustomerId" value="">
                        <input type="hidden" class="invoiceid" name="invoiceid" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-xs btn-danger cancelInvSent">Cancel</button>
                            <button type="submit" class="btn btn-xs btn-success">OK</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="final-invoice-msg-sent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Send Final Invoice</h4>
                    </div>
                    <form class="customeraddform" id="customeraddform" action="mailAttach.php" method="post">
                        <div class="modal-body">
                            <div class="invoice-mail-confirm">
                                Do you want to send this invoice to the client via email?
                                <br>
                                <strong>Note:</strong> A copy of the email and document will also be shared at <strong>certification@aitf.co.in</strong>.
                            </div>
                        </div>
                        <input type="hidden" class="finalInvoiceFirmId" name="invoiceFirmId" value="">
                        <input type="hidden" class="finalInvoiceCustomerId" name="invoiceCustomerId" value="">
                        <input type="hidden" class="finalInvoiceid" name="invoiceid" value="">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-xs btn-danger cancelFinalInvSent">Cancel</button>
                            <button type="submit" class="btn btn-xs btn-success">OK</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Keep all page content within the page-content inset div! -->
        <!-- Premium Toolbar: Tabs + Filters on one line -->
        <div class="invoice-toolbar">
            <nav class="invoice-tabs-nav" role="tablist" aria-label="Invoice Tabs">
                <button type="button" class="invoice-tab active" data-target="finalPanel" role="tab" aria-selected="true">Final Invoice</button>
                <button type="button" class="invoice-tab" data-target="proformaPanel" role="tab" aria-selected="false">Proforma Invoice</button>
            </nav>
            <!-- Final Invoice filters (shown by default) -->
            <div class="filter-set active" data-for="finalPanel">
                <div class="filter-field">
                    <label for="filterFromDate">From Date</label>
                    <input type="text" class="filterFromDate" id="filterFromDate" name="filterFromDate" placeholder="From Date" value="" autocomplete="off">
                </div>
                <div class="filter-field">
                    <label for="filterToDate">To Date</label>
                    <input type="text" class="filterToDate" id="filterToDate" name="filterToDate" placeholder="To Date" value="" autocomplete="off">
                </div>
                <div class="filter-field">
                    <label for="finalClientSearch">Client Name</label>
                    <input type="text" id="finalClientSearch" placeholder="Search by client name">
                </div>
                <div class="filter-field btn-col">
                    <button type="button" class="btn-download downloadFinalReport">
                        <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download Report
                    </button>
                </div>
            </div>
            <!-- Proforma Invoice filters -->
            <div class="filter-set" data-for="proformaPanel">
                <div class="filter-field">
                    <label for="proformaFromDate">From Date</label>
                    <input type="text" class="proformaFromDate" id="proformaFromDate" name="proformaFromDate" placeholder="From Date" value="" autocomplete="off">
                </div>
                <div class="filter-field">
                    <label for="proformaToDate">To Date</label>
                    <input type="text" class="proformaToDate" id="proformaToDate" name="proformaToDate" placeholder="To Date" value="" autocomplete="off">
                </div>
                <div class="filter-field">
                    <label for="proformaClientSearch">Client Name</label>
                    <input type="text" id="proformaClientSearch" placeholder="Search by client name">
                </div>
                <div class="filter-field btn-col">
                    <button type="button" class="btn-download downloadProformaReport">
                        <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download Report
                    </button>
                </div>
            </div>
        </div>

        <div class="page-content inset">
            <div class="row">                
                <div class="col-md-12">
                    <?php if (isset($_GET["mailsend"]) && $_GET["mailsend"] == "1") { ?>
                        <div class="alert alert-success">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            Mail has been sent successfully. A copy of the email and document has also been shared at certification@aitf.co.in.
                        </div>
                    <?php } elseif (isset($_GET["mailsend"]) && $_GET["mailsend"] == "0") { ?>
                        <div class="alert alert-danger">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <?php echo !empty($_GET["mailerror"]) ? htmlspecialchars(urldecode($_GET["mailerror"]), ENT_QUOTES, 'UTF-8') : 'Invoice mail send failed'; ?>
                        </div>
                    <?php } ?>
                    <div id="proformaPanel" class="invoice-tab-panel">
                    <div class="invoice-table-card">
                        <div class="row clearfix">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered table-striped table-hover table-sortable" id="table_proforma_invoice">
                                            <thead>
                                                <tr>
                                                    <th class=""  >
                                                        <small><a>Sr.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>GSTIN No</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Invoice No.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Net Price</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>CGST</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>SGST</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>IGST</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Particulars</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Action</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($proformaDetails as $proforma) {
                                                    $perticuler1 = "";
                                                    if (isset($proformaSvcMap[$proforma['invoice_id']])) {
                                                        $perticuler1 = implode(', ', $proformaSvcMap[$proforma['invoice_id']]);
                                                    }
                                                    $count = $count + 1;
                                                    $statusClass = (isset($proforma['invoice_status']) && $proforma['invoice_status'] == 'Rcv') ? 'payment-rcv' : 'payment-not-rcv';
                                                    $cgstShow = ($proforma['cgst'] === "" || $proforma['cgst'] === null || strtolower($proforma['cgst']) === 'na') ? '0' : $proforma['cgst'];
                                                    $sgstShow = ($proforma['sgst'] === "" || $proforma['sgst'] === null || strtolower($proforma['sgst']) === 'na') ? '0' : $proforma['sgst'];
                                                    $igstShow = ($proforma['igst'] === "" || $proforma['igst'] === null || strtolower($proforma['igst']) === 'na') ? '0' : $proforma['igst'];
                                                    ?>
                                                    <tr id='addr0' class="<?php echo $statusClass; ?>">
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $count ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['customer_name'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['gstin_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['profarma_prefix'] . '-' . $proforma['invoice_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo simpleDateConversion($proforma['invoice_date']) ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['net_price'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $cgstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $sgstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $igstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $proforma['final_amount'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $proforma['customer_id'] ?>" data-name="sel" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $perticuler1; ?></small>
                                                        </td>
                                                        <?php
                                                        if ($proforma['pro_inv_sent_count'] == '0') {
                                                            $evnsent = "fa-envelope-o";
                                                            $msg = "Send Proforma Invoice";
                                                        } else {
                                                            $evnsent = "fa-envelope";
                                                            $msg = "Re-send Proforma Invoice";
                                                        }
                                                        ?>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <a class="finalInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" data-invoice="<?php echo $proforma['invoice_no'] ?>" title="Make Final Invoice" style="padding: 1px 5px; "><i class="fa fa-plus-circle" style="padding:3px"></i></a>
                                                            <a class="editInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Edit Proforma Invoice" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                            <a class="viewInvoice" data-firm="<?php echo $proforma['firm_id'] ?>" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Proforma Invoice PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <a class="mailProformaInvoice" data-firm="<?php echo $proforma['firm_id'] ?>" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="<?php echo $msg; ?>" style="padding: 1px 5px; "><i class="fa <?php echo $evnsent; ?>" style="padding:3px"></i></a>
                                                            <a class="deleteInvoice" data-customer="<?php echo $proforma['customer_id'] ?>" data-id="<?php echo $proforma['invoice_id'] ?>" title="Delete Invoice" style="padding: 1px 5px; "><i class="fa fa-trash" style="padding:3px"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                    </div><!-- /invoice-table-card -->
                    </div><!-- /proformaPanel -->
                </div>
            </div>
        </div>

        <div class="page-content inset invoice-section-divider">
            <div class="row">
                <div class="col-md-12">
                    <div id="finalPanel" class="invoice-tab-panel active">
                    <div class="invoice-table-card">
                        <div class="row clearfix">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered table-striped table-hover table-sortable" id="table_final_invoice">
                                            <thead>
                                                <tr>
                                                    <th class="">
                                                        <small><a>Sr.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Client Name</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>GSTIN No</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Invoice No.</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Date</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>Net Price</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>CGST</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>SGST</a></small>
                                                    </th>
                                                    <th class="">
                                                        <small><a>IGST</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Amount</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Particulars</a></small>
                                                    </th>
                                                    <th class="" >
                                                        <small><a>Action</a></small>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($finalDetails as $final) {
                                                    $perticuler2 = "";
                                                    if (isset($finalSvcMap[$final['invoice_id']])) {
                                                        $perticuler2 = implode(', ', $finalSvcMap[$final['invoice_id']]);
                                                    }
                                                    $count = $count + 1;
                                                    $statusClass = (isset($final['invoice_status']) && $final['invoice_status'] == 'Rcv') ? 'payment-rcv' : 'payment-not-rcv';
                                                    $cgstShow = ($final['cgst'] === "" || $final['cgst'] === null || strtolower($final['cgst']) === 'na') ? '0' : $final['cgst'];
                                                    $sgstShow = ($final['sgst'] === "" || $final['sgst'] === null || strtolower($final['sgst']) === 'na') ? '0' : $final['sgst'];
                                                    $igstShow = ($final['igst'] === "" || $final['igst'] === null || strtolower($final['igst']) === 'na') ? '0' : $final['igst'];
                                                    ?>
                                                    <tr id='addr0' class="<?php echo $statusClass; ?>">
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $count ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["customer_name"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["gstin_no"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final['final_prefix'] . '-' . $final['final_invoice_no'] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><span><?php echo simpleDateConversion($final['invoice_date']) ?>~</span><?php echo simpleDateConversion($final['invoice_date']) ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["net_price"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $cgstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $sgstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $igstShow ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $final["final_amount"] ?></small>
                                                        </td>
                                                        <td class="customerAccount" data-id="<?php echo $final['customer_id'] ?>" data-name="desc" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                                            <small><?php echo $perticuler2; ?></small>
                                                        </td>
                                                        <?php
                                                        if ($final['final_inv_sent_count'] == '0') {
                                                            $finalevnsent = "fa-envelope-o";
                                                            $finalmsg = "Send Final Invoice";
                                                        } else {
                                                            $finalevnsent = "fa-envelope";
                                                            $finalmsg = "Re-send Final Invoice";
                                                        }
                                                        ?>
                                                        <td data-name="sel" style="border-top: 1px solid #ffffff;  border-right: 1px solid #ffffff;">
                                                            <a class="editFinalInvoice" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Edit Final Invoice" style="padding: 1px 5px; "><i class="fa fa-pencil-square" style="padding:3px"></i></a>
                                                            <a class="viewFinalInvoice" data-firm="<?php echo $final['firm_id'] ?>" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Final Invoice PDF" style="padding: 1px 5px; "><i class="fa fa-file-pdf-o" style="padding:3px"></i></a>
                                                            <?php if(Utitlity::checkForMainAdmin()){ ?>
                                                            <a class="deleteInvoice" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="Delete Final Invoice" style="padding: 1px 5px; "><i class="fa fa-trash" style="padding:3px"></i></a>
                                                            <?php } ?>
                                                            <a class="mailInvoice" data-firm="<?php echo $final['firm_id'] ?>" data-customer="<?php echo $final['customer_id'] ?>" data-id="<?php echo $final['invoice_id'] ?>" title="<?php echo $finalmsg; ?>" style="padding: 1px 5px; "><i class="fa <?php echo $finalevnsent; ?>" style="padding:3px"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                    </div><!-- /invoice-table-card -->
                    </div><!-- /finalPanel -->
                </div>
            </div>
        </div>

        <div id="delete-details1" style="display: none">
            <div id="loading-div" class="ui-corner-all">
                <span style="font-size:30pt;"><i style="padding:5px;" class="fa fa-warning text-warning"></i></span>
                <div> 
                    <span style="font-size:11pt;padding:10px" class="text-primary meeting-cancel-confirm-message" >Are you sure that you want to delete details?</span>
                </div>
                <div style="padding:3px;">
                    <button title="Click here to delete"  class="btn btn-primary btn-round btn-round-sm btn-sm delete-confirm-ok">Ok</button>
                    <button title="Click here to cancel" class="btn btn-danger  btn-round btn-round-sm btn-sm delete-cancel">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Report chooser: Step 1 pick format (PDF/Excel), Step 2 pick action (download / email) -->
        <div id="report-format-chooser" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.45); z-index:99999;">
            <div style="max-width:440px; margin:10% auto; background:#fff; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.3); overflow:hidden;">
                <div style="background:#0d4d8b; color:#fff; padding:14px 18px; font-size:15px; font-weight:600;">
                    <i class="fa fa-cloud-download" style="margin-right:8px;"></i> Download Report
                </div>

                <!-- STEP 1: format -->
                <div class="report-step report-step-format" style="padding:22px 18px; text-align:center;">
                    <p style="font-size:13px; color:#333; margin:0 0 18px;">Which format would you like?</p>
                    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                        <button type="button" class="btn btn-danger report-format-pdf" style="min-width:130px;">
                            <i class="fa fa-file-pdf-o"></i> PDF
                        </button>
                        <button type="button" class="btn btn-success report-format-excel" style="min-width:130px;">
                            <i class="fa fa-file-excel-o"></i> Excel
                        </button>
                    </div>
                    <div style="margin-top:18px;">
                        <button type="button" class="btn btn-default btn-sm report-format-cancel">Cancel</button>
                    </div>
                </div>

                <!-- STEP 2: action (download or email) -->
                <div class="report-step report-step-action" style="display:none; padding:22px 18px; text-align:center;">
                    <p style="font-size:13px; color:#333; margin:0 0 4px;">Selected format: <strong class="report-format-label">Excel</strong></p>
                    <p style="font-size:12px; color:#777; margin:0 0 18px;">How would you like to receive it?</p>
                    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                        <button type="button" class="btn btn-primary report-action-download" style="min-width:150px;">
                            <i class="fa fa-download"></i> Download Report
                        </button>
                        <button type="button" class="btn btn-warning report-action-email" style="min-width:150px;">
                            <i class="fa fa-envelope"></i> Get Report on Email
                        </button>
                    </div>

                    <!-- Email sub-form -->
                    <div class="report-email-box" style="display:none; margin-top:18px; text-align:left;">
                        <label style="font-size:12px; font-weight:600; color:#333;">Enter email address</label>
                        <input type="email" class="report-email-input form-control" placeholder="name@example.com"
                               style="margin-top:6px;" autocomplete="off">
                        <div class="report-email-msg" style="font-size:12px; margin-top:8px; min-height:16px;"></div>
                        <button type="button" class="btn btn-success report-email-send" style="width:100%; margin-top:6px;">
                            <i class="fa fa-paper-plane"></i> Get Report
                        </button>
                    </div>

                    <div style="margin-top:18px;">
                        <button type="button" class="btn btn-default btn-sm report-step-back">Back</button>
                        <button type="button" class="btn btn-default btn-sm report-format-cancel">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .btn-circle {
        width: 30px;
        height: 30px;
        text-align: center;
        padding: 6px 0;
        font-size: 12px;
        line-height: 1.428571429;
        border-radius: 15px;
    }
    #loading-div-background{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }

    #loading-div{
        width: 300px;
        height: 150px;
        background-color: #fff;
        color:#1468b3;
        border: 5px solid #1468b3;
        text-align: center;
        color: #202020;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -150px;
        margin-top: -100px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        opacity: 1.0;
        behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
    }

    #loading-div-user-info{
        width: 350px;
        background-color: #fff;
        color:#1468b3;
        border: 1px solid gray;
        color: #202020;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -150px;
        margin-top: -100px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        opacity: 1.0;
        behavior: url("/css/pie/PIE.htc"); /* HANDLES IE */
    }

    #loading-div-background-resend{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }

    #loading-div-background-meetedit{
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        height: 100%;
    }
</style>

<script type="text/javascript">
    function debounce(callback, delay) {
        var debounceTimer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);
        };
    }

    var proformaTable = $('#table_proforma_invoice').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        "autoWidth": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            { "width": "5%", "targets": 0 },
            { "width": "12%", "targets": 1 },
            { "width": "10%", "targets": 2 },
            { "width": "8%", "targets": 3 },
            { "width": "8%", "targets": 4 },
            { "width": "8%", "targets": 5 }, // Net Price
            { "width": "6%", "targets": 6 }, // CGST
            { "width": "6%", "targets": 7 }, // SGST
            { "width": "6%", "targets": 8 }, // IGST
            { "width": "8%", "targets": 9 }, // Amount
            { "width": "15%", "targets": 10 } // Particulars
        ]
    });

    var finalTable = $('#table_final_invoice').DataTable({
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        "autoWidth": false,
//            "pageLength": 10,
        "paging": false,
        "columnDefs": [
            { "width": "5%", "targets": 0 },
            { "width": "12%", "targets": 1 },
            { "width": "10%", "targets": 2 },
            { "width": "8%", "targets": 3 },
            { "width": "8%", "targets": 4 },
            { "width": "8%", "targets": 5 }, // Net Price
            { "width": "6%", "targets": 6 }, // CGST
            { "width": "6%", "targets": 7 }, // SGST
            { "width": "6%", "targets": 8 }, // IGST
            { "width": "8%", "targets": 9 }, // Amount
            { "width": "15%", "targets": 10 } // Particulars
        ]
    });

    $('.filterFromDate, .filterToDate').change(function () {
        var fromDate = $(".filterFromDate").val();
        var toDate = $(".filterToDate").val();
        if (fromDate != "" && toDate != "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date, From Date se chhoti nahi ho sakti");
                $(".filterToDate").val("");
            }
        }
        finalTable.draw();
    });

    $('.proformaFromDate, .proformaToDate').change(function () {
        var fromDate = $(".proformaFromDate").val();
        var toDate = $(".proformaToDate").val();
        if (fromDate != "" && toDate != "") {
            if (!compareMinDates(fromDate, toDate)) {
                alert("To Date, From Date se chhoti nahi ho sakti");
                $(".proformaToDate").val("");
            }
        }
        proformaTable.draw();
    });

    function ShowProgressAnimation() {
//        alert("called");
        $("#loading-div-background").show();
//        $("#loading-div-background-resend").show();
    }
    function HideProgressAnimation() {
//        alert("called");
        $("#loading-div-background").hide();
//        $("#loading-div-background-resend").show();
    }

    function compareMinDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        if (filterDateSplit[2] < rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] < rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] < rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    function compareMaxDates(filterDate, rowDate) {
        var filterDateSplit = filterDate.split(".");
        var rowDateSplit = rowDate.split(".");
        //year comparison
        if (filterDateSplit[2] > rowDateSplit[2]) {
            return true;
        } else if (filterDateSplit[2] == rowDateSplit[2]) {
            if (filterDateSplit[1] > rowDateSplit[1]) {
                return true;
            } else if (filterDateSplit[1] == rowDateSplit[1]) {
                if (filterDateSplit[0] > rowDateSplit[0]) {
                    return true;
                } else if (filterDateSplit[0] == rowDateSplit[0]) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    $.fn.DataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var tableId = settings.nTable.id;
                var fromDate, toDate;
                if (tableId === 'table_final_invoice') {
                    fromDate = $("#filterFromDate").val();
                    toDate = $("#filterToDate").val();
                } else if (tableId === 'table_proforma_invoice') {
                    fromDate = $("#proformaFromDate").val();
                    toDate = $("#proformaToDate").val();
                } else {
                    return true;
                }
                var isfromDate = false;
                var isToDate = false;
                if (fromDate == "" && toDate == "") {
                    return true;
                }
                var dateValue = data[4] || "";
                var dateSplit = dateValue.split("~");
                // Final invoice date cell is "date~date"; proforma is a single "date".
                var rowDate = dateSplit.length > 1 ? dateSplit[1] : dateSplit[0];
                if (fromDate != "") {
                    isfromDate = compareMinDates(fromDate, rowDate);
                } else {
                    isfromDate = true;
                }
                if (toDate != "") {
                    isToDate = compareMaxDates(toDate, rowDate);
                } else {
                    isToDate = true;
                }
                return (isfromDate && isToDate);
            }
    );

    $(document).ready(function () {

        // ===== Premium Tab Navigation switching =====
        $('.invoice-tab').on('click', function () {
            var target = $(this).data('target');
            if ($(this).hasClass('active')) { return; }
            $('.invoice-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            $('.invoice-tab-panel').removeClass('active');
            $('#' + target).addClass('active');
            // Toggle the matching filter set in the toolbar
            $('.filter-set').removeClass('active');
            $('.filter-set[data-for="' + target + '"]').addClass('active');
            // Recalculate DataTable column widths after visibility change
            setTimeout(function () {
                if (target === 'proformaPanel' && typeof proformaTable !== 'undefined') {
                    proformaTable.columns.adjust().draw();
                } else if (target === 'finalPanel' && typeof finalTable !== 'undefined') {
                    finalTable.columns.adjust().draw();
                }
            }, 50);
        });

        var debouncedProformaSearch = debounce(function () {
            proformaTable.column(1).search($('#proformaClientSearch').val()).draw();
        }, 200);

        var debouncedFinalSearch = debounce(function () {
            finalTable.column(1).search($('#finalClientSearch').val()).draw();
        }, 200);

        $('#proformaClientSearch').on('input', debouncedProformaSearch);
        $('#finalClientSearch').on('input', debouncedFinalSearch);

        // Holds the report parameters chosen when a Download Report button is clicked.
        var pendingReport = null;
        // Format selected in step 1 ('pdf' | 'excel').
        var pendingFormat = null;

        // Download via XHR->Blob (same path for PDF and Excel). This never navigates
        // the page, so there is no browser tab spinner; the file is fetched in the
        // background and pushed to the browser's download bar via a temporary <a download>.
        // Result: PDF behaves exactly like Excel.
        function submitReportDownload() {
            if (!pendingReport || !pendingFormat) {
                return;
            }
            var url = (pendingFormat === 'excel') ? 'exportInvoiceReportExcel.php' : 'exportInvoiceReport.php';
            var fallbackName = (pendingFormat === 'excel') ? 'Invoice_Report.xlsx' : 'Invoice_Report.pdf';

            var $btn = $('.report-action-download');
            if (!$btn.data('orig')) { $btn.data('orig', $btn.html()); }
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Preparing...');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                if (xhr.status === 200) {
                    var cd = xhr.getResponseHeader('Content-Disposition') || '';
                    var m = /filename="?([^"]+)"?/.exec(cd);
                    var fname = m ? m[1] : fallbackName;
                    var objUrl = URL.createObjectURL(xhr.response);
                    var link = document.createElement('a');
                    link.href = objUrl;
                    link.download = fname;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(function () { URL.revokeObjectURL(objUrl); }, 4000);
                    closeFormatChooser();
                } else {
                    alert('Download failed. Please try again.');
                }
            };
            xhr.onerror = function () {
                $btn.prop('disabled', false).html($btn.data('orig'));
                alert('Download failed. Please try again.');
            };

            var params = 'invoiceType=' + encodeURIComponent(pendingReport.invoiceType) +
                         '&fromDate=' + encodeURIComponent(pendingReport.fromDate) +
                         '&toDate=' + encodeURIComponent(pendingReport.toDate) +
                         '&clientName=' + encodeURIComponent(pendingReport.clientName);
            xhr.send(params);
        }

        function showReportStep(step) {
            $('.report-step').hide();
            if (step === 'action') {
                $('.report-step-action').show();
            } else {
                $('.report-step-format').show();
            }
        }

        function resetChooser() {
            pendingFormat = null;
            $('.report-email-box').hide();
            $('.report-email-input').val('');
            $('.report-email-msg').text('');
            $('.report-email-send').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            showReportStep('format');
        }

        function openFormatChooser(params) {
            pendingReport = params;
            resetChooser();
            $('#report-format-chooser').css('display', 'block');
        }

        function closeFormatChooser() {
            $('#report-format-chooser').hide();
            pendingReport = null;
            pendingFormat = null;
        }

        // --- Step 1: choose format ---
        $('.report-format-pdf').click(function () {
            pendingFormat = 'pdf';
            $('.report-format-label').text('PDF');
            $('.report-email-box').hide();
            showReportStep('action');
        });

        $('.report-format-excel').click(function () {
            pendingFormat = 'excel';
            $('.report-format-label').text('Excel');
            $('.report-email-box').hide();
            showReportStep('action');
        });

        // --- Step 2: download (async; closes the chooser itself once the file is ready) ---
        $('.report-action-download').click(function () {
            submitReportDownload();
        });

        // --- Step 2: reveal the email sub-form ---
        $('.report-action-email').click(function () {
            $('.report-email-msg').text('');
            $('.report-email-box').show();
            $('.report-email-input').focus();
        });

        // --- Step 2 (email): send the report ---
        $('.report-email-send').click(function () {
            if (!pendingReport || !pendingFormat) {
                return;
            }
            var email = $.trim($('.report-email-input').val());
            var $msg = $('.report-email-msg');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                $msg.css('color', '#cc0000').text('Please enter a valid email address.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending report, please wait...');

            $.post('emailInvoiceReport.php', {
                invoiceType: pendingReport.invoiceType,
                fromDate: pendingReport.fromDate,
                toDate: pendingReport.toDate,
                clientName: pendingReport.clientName,
                format: pendingFormat,
                email: email
            }, null, 'json').done(function (res) {
                if (res && res.success) {
                    $msg.css('color', '#1a7f37').text(res.message || 'Report sent successfully.');
                    setTimeout(closeFormatChooser, 2000);
                } else {
                    $msg.css('color', '#cc0000').text((res && res.message) ? res.message : 'Could not send the email.');
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
                }
            }).fail(function () {
                $msg.css('color', '#cc0000').text('Server error. Please try again.');
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Get Report');
            });
        });

        $('.report-step-back').click(function () {
            resetChooser();
        });

        $('.report-format-cancel').click(function () {
            closeFormatChooser();
        });

        // Click on the dark backdrop (outside the dialog box) closes the chooser.
        $('#report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeFormatChooser();
            }
        });

        $('.downloadProformaReport').click(function () {
            openFormatChooser({
                invoiceType: 'proforma',
                fromDate: $('#proformaFromDate').val(),
                toDate: $('#proformaToDate').val(),
                clientName: $('#proformaClientSearch').val()
            });
        });

        $('.downloadFinalReport').click(function () {
            openFormatChooser({
                invoiceType: 'final',
                fromDate: $('#filterFromDate').val(),
                toDate: $('#filterToDate').val(),
                clientName: $('#finalClientSearch').val()
            });
        });

        $('#filterFromDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $("#filterFromDate").keydown(function (e) {
            //            e.preventDefault();
        });

        $('#filterToDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $('#proformaFromDate, #proformaToDate').datepicker({
            minDate: '-100Y',
            maxDate: '+100Y',
            changeYear: true,
            changeMonth: true,
            yearRange: "-20:+100",
            dateFormat: 'dd.mm.yy'
        });

        $("#filterToDate").keydown(function (e) {
            //            e.preventDefault();
        });

        $('.viewInvoice').click(function () {
            var customerID = $(this).attr('data-customer');
            var firmId = $(this).attr('data-firm');
            var invoiceid = $(this).attr('data-id');
            // open popup
            var url = 'invoicePdf.php'
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="invoiceconsultingFirmName" value="' + customerID + '" />' +
                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.viewFinalInvoice').click(function () {
            var customerID = $(this).attr('data-customer');
            var firmId = $(this).attr('data-firm');
            var invoiceid = $(this).attr('data-id');
            // open popup
            var url = 'finalInvoicePDF.php'
            var form = $('<form action="' + url + '" method="post" target="_blank">' +
                    '<input type="hidden" name="invoiceCustomerId" value="' + customerID + '" />' +
                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $(".mailProformaInvoice").click(function () {
//            var firmanme = $(this).attr('data-firmname');
//            alert($(this).attr('data-firmname'));
            $(".invoiceFirmId").val($(this).attr('data-firm'));
            $(".invoiceCustomerId").val($(this).attr('data-customer'));
            $(".invoiceid").val($(this).attr('data-id'));

            $("#invoice-msg-sent").modal("show");
//            ShowProgressAnimation();
//            var customerID = $(this).attr('data-customer');
//            var firmId = $(this).attr('data-firm');
//            var invoiceid = $(this).attr('data-id');
//            // open popup
//            var url = 'invoicemailsend.php'
//            var form = $('<form action="' + url + '" method="post" >' +
//                    '<input type="hidden" name="invoiceCustomerId" value="' + customerID + '" />' +
//                    '<input type="hidden" name="invoiceFirmId" value="' + firmId + '" />' +
//                    '<input type="hidden" name="invoiceid" value="' + invoiceid + '" />' +
//                    '</form>');
//            $('body').append(form);
//            form.submit();
        });

        $(".cancelInvSent").click(function () {
            $(".invoiceFirmId").val("");
            $(".invoiceCustomerId").val("");
            $(".invoiceid").val("");

            $("#invoice-msg-sent").modal("hide");
        });

        $(".mailInvoice").click(function () {

            $(".finalInvoiceFirmId").val($(this).attr('data-firm'));
            $(".finalInvoiceCustomerId").val($(this).attr('data-customer'));
            $(".finalInvoiceid").val($(this).attr('data-id'));

            $("#final-invoice-msg-sent").modal("show");

        });

        $(".cancelFinalInvSent").click(function () {
            $(".finalInvoiceFirmId").val("");
            $(".finalInvoiceCustomerId").val("");
            $(".finalInvoiceid").val("");

            $("#final-invoice-msg-sent").modal("hide");
        });

        $('.customerAccount').click(function () {
            var customerID = $(this).attr('data-id');
//            alert(customerID);
            // open popup
            localStorage['visited'] = customerID;
            var url = 'customerAccount.php'
            var form = $('<form action="' + url + '" method="get">' +
                    '<input type="hidden" name="customerid" value="' + customerID + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
        });

        // Enable DataTables with paging to reduce render cost; guard against reinit
        if ($.fn.DataTable) {
            if (!$.fn.DataTable.isDataTable('#table_proforma_invoice')) {
                $("#table_proforma_invoice").DataTable({
                    pageLength: 50,
                    deferRender: true,
                    lengthChange: false
                });
            }
            if (!$.fn.DataTable.isDataTable('#table_final_invoice')) {
                $("#table_final_invoice").DataTable({
                    pageLength: 50,
                    deferRender: true,
                    lengthChange: false
                });
            }
        }

        $('.finalInvoice').click(function () {
            var invoice = $(this).attr('data-id');
            var customer = $(this).attr('data-customer');
            var invoiceNo = $(this).attr('data-invoice');
            var url = 'finalInvoice.php'
            var form = $('<form action="' + url + '" method="post">' +
                    '<input type="hidden" name="invoice" value="' + invoice + '" />' +
                    '<input type="hidden" name="customer" value="' + customer + '" />' +
                    '<input type="hidden" name="invoiceNo" value="' + invoiceNo + '" />' +
                    '</form>');
            $('body').append(form);
            form.submit();
        });

        $('.editFinalInvoice').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    invoice: $(this).attr('data-id'),
                },
                success: function (data)
                {

                    $("input[value='" + data[0]['tax_type'] + "']").prop('checked', true);
                    $('.invoiceconsultingFirmName').val(data[2]['id']);
                    $('.invoiceAddress').val(data[2]['address']);
                    $('.invoiceDt').val(moment(data[0]['invoice_date']).format('DD.MM.YYYY'));
                    $(".invoiceState").val(data[0]['state']);
                    $(".invoiceStateCode").val(data[0]['state_code']);
                    $('.finalNetPrice').val(data[0]['net_price']);
                    $('.serviceTax').val(data[0]['svc_tax_code']);
//                    alert(data[0]['swachh_bharat_cess']);
                    if (data[0]['tax_type'] == "CGST & SGST") {
                        $('.finalcgst').val(data[0]['cgst']);
                        $('.finalsgst').val(data[0]['sgst']);
                        $('.finaligst').val("");
                    } else if (data[0]['tax_type'] == "IGST") {
                        $('.finaligst').val(data[0]['igst']);
                        $('.finalcgst').val("");
                        $('.finalsgst').val("");
                    }
//                    $(".finaltaxtype").val(data[0]['tax_type']);

                    $('.invoiceCustomerId').val(data[0]['customer_id']);
                    $('.invoiceFirmId').val(data[0]['firm_id']);
                    $('.invoiceId').val(data[0]['invoice_id']);
                    $('.finalserviceAmtTd').empty();
                    var includeFinalQtyRate = false;
                    for (var k in data[1]) {
                        if ((data[1][k]['quantity'] !== null && data[1][k]['quantity'] !== undefined && data[1][k]['quantity'] !== '') ||
                                (data[1][k]['rate'] !== null && data[1][k]['rate'] !== undefined && data[1][k]['rate'] !== '')) {
                            includeFinalQtyRate = true;
                            break;
                        }
                    }
                    $('.includeFinalQtyRate').prop('checked', includeFinalQtyRate);
                    var option1 = "";
                    var svcs = '<?php echo $svclist ?>';
                    var services = svcs.split(',');
                    var serviceLen = services.length;
                    var max = (Math.max(serviceLen));
                    for (var key = 0; key < max; key++) {
//                        alert("in");
                        var svcname = key <= serviceLen && services[key] != null && services[key] != "" ? services[key] : "";
                        option1 += '<option value="' + svcname + '">' + svcname + '</option>';
                    }

                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['service_desc'] + '"selected>' + data[1][key]['service_desc'] + '</option>';
                        if (includeFinalQtyRate) {
                            var qtyVal = data[1][key]['quantity'] !== undefined && data[1][key]['quantity'] !== null ? data[1][key]['quantity'] : '';
                            var rateVal = data[1][key]['rate'] !== undefined && data[1][key]['rate'] !== null ? data[1][key]['rate'] : '';
                            $('.finalserviceAmtTd').append('<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-5 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceQty[]" class="form-control form-group finalInvoiceServiceQty" placeholder="Qty" value="' + qtyVal + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="finalInvoiceServiceRate[]" class="form-control form-group finalInvoiceServiceRate" placeholder="Rate" value="' + rateVal + '"></div>\n\
                                        <div class = "col-sm-2 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '" readonly> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        } else {
                            $('.finalserviceAmtTd').append('<div class="row finalRemoveServiceAmtRow finalInvoiceServiceRow">\n\
                                        <div class="col-sm-8 form-group float-label-control">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control finalInvoiceService dropdown-toggle" name="finalInvoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "finalInvoiceServiceAmt[]" class ="form-control form-group finalInvoiceServiceAmt" id = "finalInvoiceServiceAmt" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '"> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 finalRemoveServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="finalRemoveServiceAmt pull-right" id="finalRemoveServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        }
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#finalinvoiceModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });

        $('.editInvoice').click(function () {
            $.ajax({
                url: "getInvoiceInfo.php",
                type: "GET",
                dataType: "json",
                data: {
                    invoice: $(this).attr('data-id'),
                },
                success: function (data)
                {
//                    alert(data);
                    $('.invoiceconsultingFirmName').val(data[2]['id']);
                    $('#invoiceAddress').val(data[2]['address']);
                    $('.invoiceDt').val(moment(data[0]['invoice_date']).format('DD.MM.YYYY'));
                    $(".invoiceState").val(data[0]['state']);
                    $(".invoiceStateCode").val(data[0]['state_code']);
                    $('.netPrice').val(data[0]['net_price']);
                    $('.serviceTax').val(data[0]['svc_tax_code']);
//                    alert(data[0]['swachh_bharat_cess']);
                    if (data[0]['tax_type'] == "CGST & SGST") {
                        $('.cgst').val(data[0]['cgst']);
                        $('.sgst').val(data[0]['sgst']);
                        $('.igst').val("");
                    } else if (data[0]['tax_type'] == "IGST") {
                        $('.igst').val(data[0]['igst']);
                        $('.cgst').val("");
                        $('.sgst').val("");
                    }
//                    $(".taxtype").val(data[0]['tax_type']);
                    $("input[value='" + data[0]['tax_type'] + "']").prop('checked', true);
                    $('.invoiceCustomerId').val(data[0]['customer_id']);
                    $('.invoiceFirmId').val(data[0]['firm_id']);
                    $('.invoiceId').val(data[0]['invoice_id']);
                    $('.serviceAmtTd').empty();
                    var includeQtyRate = false;
                    for (var k in data[1]) {
                        if ((data[1][k]['quantity'] !== null && data[1][k]['quantity'] !== undefined && data[1][k]['quantity'] !== '') ||
                                (data[1][k]['rate'] !== null && data[1][k]['rate'] !== undefined && data[1][k]['rate'] !== '')) {
                            includeQtyRate = true;
                            break;
                        }
                    }
                    $('.includeQtyRate').prop('checked', includeQtyRate);
                    var option1 = "";
                    var svcs = '<?php echo $svclist ?>';
                    var services = svcs.split(',');
                    var serviceLen = services.length;
                    var max = (Math.max(serviceLen));
                    for (var key = 0; key < max; key++) {
//                        alert("in");
                        var svcname = key <= serviceLen && services[key] != null && services[key] != "" ? services[key] : "";
                        option1 += '<option value="' + svcname + '">' + svcname + '</option>';
                    }

                    for (var key in data[1]) {
                        var option2 = '<option value="' + data[1][key]['service_desc'] + '"selected>' + data[1][key]['service_desc'] + '</option>';
                        if (includeQtyRate) {
                            var qtyVal = data[1][key]['quantity'] !== undefined && data[1][key]['quantity'] !== null ? data[1][key]['quantity'] : '';
                            var rateVal = data[1][key]['rate'] !== undefined && data[1][key]['rate'] !== null ? data[1][key]['rate'] : '';
                            $('.serviceAmtTd').append('<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-5 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Qty</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceQty[]" class="form-control form-group invoiceServiceQty" placeholder="Qty" value="' + qtyVal + '"></div>\n\
                                        <div class="col-sm-2 form-group float-label-control" style="padding-top:10px;">\n\
                                        <label for="">Rate</label>\n\
                                        <input type="number" step="0.01" name="invoiceServiceRate[]" class="form-control form-group invoiceServiceRate" placeholder="Rate" value="' + rateVal + '"></div>\n\
                                        <div class = "col-sm-2 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style = "padding-top:10px;" >\n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "invoiceServiceAmt[]" class ="form-control form-group invoiceServiceAmt" id = "invoiceService" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '" readonly> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        } else {
                            $('.serviceAmtTd').append('<div class="row removeServiceAmtRow invoiceServiceRow" style="padding-top: 10px;">\n\
                                        <div class="col-sm-8 form-group float-label-control invoiceServiceCol">\n\
                                        <div class="input-group dropdown">\n\
                                        <label for="">Service</label>\n\
                                        <input type="text" class="form-control invoiceService dropdown-toggle" name="invoiceService[]" value="' + data[1][key]['service_desc'] + '" placeholder="Service">\n\
                                        <ul class="dropdown-menu"><?php foreach ($aitfservices as $service) { ?><li><a href="#" data-value="<?php echo $service["firm_service"] ?>"><?php echo $service["firm_service"] ?></a></li><?php } ?></ul><span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span></div></div>\n\
                                        <div class = "col-sm-3 removeSvcAmtVal form-group float-label-control invoiceAmountCol" style = "padding-top:10px;" > \n\
                                        <label for = "" > Amount </label>\n\
                                        <input type= "number" step="0.01" name= "invoiceServiceAmt[]" class ="form-control form-group invoiceServiceAmt" id = "invoiceService" placeholder = "Amount" value="' + data[1][key]['service_amount'] + '"> </input>\n\
                                        </div>\n\
                                        <div class = "col-sm-1 removeServiceAmtCol form-group float-label-control" style = "padding-top:10px;" >\n\
                                        <a  class="removeServiceAmt pull-right" id="removeServiceAmt" style="cursor: pointer;"><i style="margin-top: 9px;" class="glyphicon glyphicon-remove" title="Remove Service"></i></a>\n\
                                        </div>\n\
                                        </div>');
                        }
                    }
                    $('input[type="text"]').trigger('change');
                    $('input[type="number"]').trigger('change');
                    $('#invoiceModal').modal('show');
                },
                error: function (data, status, err)
                {
                    alert(status);
                }
            });
        });


        $("body").on("click", ".deleteInvoice", function () {
            var id = $(this).attr('data-id');

            $('#delete-details1').show();
            $('.delete-confirm-ok').click(function () {

                var url = 'deleteFinalInvoice.php';
                var form = $('<form action="' + url + '" method="post" >' +
                        '<input type="hidden" name="finalId" value="' + id + '" />' +
                        '</form>');
                $('body').append(form);
                form.submit();
            });
            $('.delete-cancel').click(function () {
                $('#delete-details1').hide();
            });
        });



    });
</script>
t>

<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Finance Page";
    include 'header.php';
    include 'dbConnect.php';
    require_once __DIR__ . '/include/payment_due_helper.php';

    $allPaymentInfo = aitf_get_due_payments($db);
    ?>
    <style>
        /* Base typography & resets */
        #page-content-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .custom-float-label-control {
            margin-bottom: 0em;
        }

        /* Section Title Badge */
        .invoice-section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px;
            padding: 8px 18px;
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.2);
            letter-spacing: -0.2px;
        }

        /* Filter Panel Card */
        .invoice-filter-panel {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin: 10px 0 20px;
            padding: 14px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .invoice-filter-item {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Search Box Specific Styling */
        .search-item {
            flex: 1.5;
            min-width: 250px;
        }

        .search-item i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #0d4d8b;
            z-index: 1;
            font-size: 14px;
        }

        .search-item input {
            padding-left: 35px !important;
        }

        .invoice-filter-item input {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 13px;
            font-weight: 500;
            background-color: #fff;
            color: #1e293b;
            transition: all 0.2s;
        }

        .invoice-filter-item input::placeholder {
            color: #94a3b8;
        }

        .invoice-filter-item input:focus {
            border-color: #0d4d8b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12);
        }

        .btn-report-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 40px;
            margin: 0;
            padding: 0 20px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: none;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .btn-solid-primary {
            background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(13, 77, 139, 0.25);
        }

        .btn-solid-primary:hover {
            background: linear-gradient(135deg, #11599e 0%, #0a467e 100%);
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.35);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-solid-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(13, 77, 139, 0.2);
        }

        #tab_customer_list_filter {
            display: none;
        }

        /* Table Container & DataTable Styling */
        .table-responsive {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        #tab_customer_list thead th {
            background: #0f2744 !important;
            color: #ffffff !important;
            padding: 12px 14px !important;
            border: none !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            vertical-align: middle !important;
        }

        #tab_customer_list thead th small,
        #tab_customer_list thead th a {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            text-decoration: none !important;
        }

        #tab_customer_list tbody td {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 10px 14px !important;
            font-size: 12.5px !important;
            color: #334155 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        #tab_customer_list tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        #tab_customer_list tbody tr:nth-of-type(odd) td {
            background-color: #fcfdfe;
        }

        /* Client Name click style */
        #tab_customer_list tbody td:first-child small {
            font-weight: 700;
            color: #0d4d8b;
            cursor: pointer;
            transition: color 0.15s ease;
            font-size: 12.5px;
        }

        #tab_customer_list tbody td:first-child:hover small {
            color: #0284c7;
            text-decoration: underline;
        }

        /* Service Type column badge */
        #tab_customer_list tbody td:nth-child(4) small {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        /* Payment Pending Amount */
        #tab_customer_list tbody td:nth-child(5) small {
            color: #dc2626;
            font-weight: 800;
            font-size: 13px;
        }

        /* Action Mail Button */
        .btn-reminder-mail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 8px;
            border: 1px solid #93c5fd;
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%);
            color: #ffffff !important;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(13, 77, 139, 0.2);
            transition: all 0.2s ease;
        }
        .btn-reminder-mail:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(13, 77, 139, 0.35);
        }

        /* Comment Textarea Styling */
        #tab_customer_list textarea.saveChanges {
            width: 100% !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            color: #1e293b !important;
            background: #f8fafc !important;
            resize: vertical !important;
            min-height: 48px;
            line-height: 1.4;
            transition: all 0.2s ease;
            outline: none;
            box-sizing: border-box;
        }

        #tab_customer_list textarea.saveChanges:focus {
            background: #ffffff !important;
            border-color: #0d4d8b !important;
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.12) !important;
        }

        /* =====================================================================
           PREMIUM DARK MODE  —  paymentPage.php
           ===================================================================== */
        html.theme-dark {
            --pp-bg:        #0a0e14;
            --pp-surface:   #111722;
            --pp-surface-2: #161d2b;
            --pp-border:    #232c3d;
            --pp-border-2:  #2e3a4f;
            --pp-text:      #dbe3f0;
            --pp-text-dim:  #93a1b8;
            --pp-text-faint:#6b7890;
            --pp-accent:    #4a9eff;
        }

        html.theme-dark #page-content-wrapper { background: var(--pp-bg); color: var(--pp-text); }

        /* Section title */
        html.theme-dark .invoice-section-title {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            color: #ffffff;
        }

        /* Filter panel */
        html.theme-dark .invoice-filter-panel {
            background: var(--pp-surface);
            border-color: var(--pp-border);
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark .search-item i { color: #7aa9f5; }
        html.theme-dark .invoice-filter-item input {
            background-color: var(--pp-surface-2);
            border-color: var(--pp-border-2);
            color: var(--pp-text);
        }
        html.theme-dark .invoice-filter-item input::placeholder { color: var(--pp-text-faint); }
        html.theme-dark .invoice-filter-item input:focus {
            border-color: var(--pp-accent);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.18);
        }

        /* Download button */
        html.theme-dark .btn-solid-primary {
            background: linear-gradient(135deg, #1d63b0 0%, #174e8c 100%);
            box-shadow: 0 4px 14px rgba(20, 104, 179, 0.4);
        }
        html.theme-dark .btn-solid-primary:hover { 
            background: linear-gradient(135deg, #2f81d6 0%, #1d63b0 100%); 
            box-shadow: 0 8px 22px rgba(20, 104, 179, 0.5); 
        }

        /* Table shell */
        html.theme-dark .table-responsive {
            background: var(--pp-surface);
            border: 1px solid var(--pp-border);
            border-radius: 12px;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.45);
        }
        html.theme-dark #tab_customer_list thead th {
            background: #090e17 !important;
            border-color: var(--pp-border-2) !important;
        }
        html.theme-dark #tab_customer_list thead th small,
        html.theme-dark #tab_customer_list thead th a { color: #f1f5f9 !important; }
        html.theme-dark #tab_customer_list small { color: var(--pp-text) !important; }
        html.theme-dark #tab_customer_list tbody td {
            border-color: var(--pp-border) !important;
            color: var(--pp-text) !important;
            background-color: var(--pp-surface) !important;
        }
        html.theme-dark #tab_customer_list tbody tr:hover td { background-color: var(--pp-surface-2) !important; }
        html.theme-dark .table-striped tbody tr:nth-of-type(odd) td { background-color: rgba(255, 255, 255, 0.02) !important; }
        html.theme-dark #tab_customer_list tbody td:first-child small { color: #38bdf8 !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(4) small { background: rgba(56, 189, 248, 0.15); color: #7dd3fc; }
        html.theme-dark #tab_customer_list tbody td:nth-child(5) small { color: #f87171 !important; }

        /* Comment textarea */
        html.theme-dark #tab_customer_list textarea,
        html.theme-dark .saveChanges {
            background-color: var(--pp-surface-2) !important;
            border: 1px solid var(--pp-border-2) !important;
            color: var(--pp-text) !important;
        }
        html.theme-dark .saveChanges:focus {
            border-color: var(--pp-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.2) !important;
        }
        html.theme-dark .saveChanges::placeholder { color: var(--pp-text-faint); }

        /* Report Modal Dark Mode */
        html.theme-dark #report-format-chooser > div {
            background: var(--pp-surface) !important;
            border: 1px solid var(--pp-border) !important;
            color: var(--pp-text) !important;
        }
        html.theme-dark #report-format-chooser p {
            color: var(--pp-text) !important;
        }
        html.theme-dark .report-email-box label {
            color: var(--pp-text) !important;
        }
        html.theme-dark .report-email-input {
            background-color: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
            color: var(--pp-text) !important;
        }

        /* =====================================================================
           ULTRA-MODERN DUE PAYMENT REMINDER MODAL
           ===================================================================== */
        #duePaymentReminderModal {
            overflow-y: auto !important;
            padding-right: 0 !important;
        }

        #duePaymentReminderModal .modal-dialog {
            max-width: 510px !important;
            margin: 25px auto !important;
        }

        #duePaymentReminderModal .modal-content {
            border-radius: 20px !important;
            overflow: hidden !important;
            border: none !important;
            box-shadow: 0 25px 60px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.08) !important;
            background: #ffffff !important;
        }

        /* Modern Clean Header (No heavy dark blue banner) */
        .modern-reminder-header {
            background: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 18px 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .modern-header-left {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
        }

        .modern-header-icon {
            width: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #2563eb !important;
            font-size: 19px !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12) !important;
            flex-shrink: 0 !important;
        }

        .modern-header-title {
            font-size: 17px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin: 0 !important;
            letter-spacing: -0.3px !important;
            line-height: 1.25 !important;
        }

        .modern-header-subtitle {
            font-size: 12px !important;
            font-weight: 500 !important;
            color: #64748b !important;
            margin: 3px 0 0 0 !important;
            line-height: 1.2 !important;
        }

        .modern-header-close {
            width: 32px !important;
            height: 32px !important;
            border-radius: 50% !important;
            background: #f1f5f9 !important;
            border: none !important;
            color: #64748b !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 20px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            outline: none !important;
            padding: 0 !important;
            line-height: 1 !important;
        }

        .modern-header-close:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            transform: scale(1.06) !important;
        }

        /* Modal Body */
        .modern-reminder-body {
            padding: 20px 24px 16px !important;
            background: #f8fafc !important;
        }

        /* Executive Summary Card */
        .modern-summary-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04) !important;
            margin-bottom: 18px !important;
        }

        .summary-client-section {
            padding: 16px 18px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 12px !important;
        }

        .summary-label {
            font-size: 10.5px !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.8px !important;
            margin-bottom: 4px !important;
            display: block !important;
        }

        .summary-client-name {
            font-size: 16px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin: 0 0 8px 0 !important;
            line-height: 1.3 !important;
            word-break: break-word !important;
        }

        .summary-meta-row {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 8px 12px !important;
            font-size: 12.5px !important;
            color: #475569 !important;
            font-weight: 500 !important;
        }

        .summary-meta-item {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .summary-meta-item i {
            color: #0d4d8b !important;
            font-size: 12px !important;
        }

        .summary-meta-sep {
            color: #cbd5e1 !important;
        }

        .overdue-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 4px 10px !important;
            border-radius: 20px !important;
            white-space: nowrap !important;
        }

        .overdue-pulse {
            width: 6px !important;
            height: 6px !important;
            border-radius: 50% !important;
            background: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2) !important;
        }

        .summary-balance-section {
            background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%) !important;
            border-top: 1px dashed #fca5a5 !important;
            padding: 14px 18px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .balance-title {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            color: #991b1b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .balance-title i {
            color: #dc2626 !important;
            font-size: 13px !important;
        }

        .balance-subtitle {
            font-size: 11px !important;
            color: #b91c1c !important;
            opacity: 0.85 !important;
            margin-top: 2px !important;
        }

        .balance-amount-display {
            font-size: 24px !important;
            font-weight: 900 !important;
            color: #dc2626 !important;
            letter-spacing: -0.5px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: baseline !important;
        }

        .balance-currency {
            font-size: 20px !important;
            font-weight: 800 !important;
            margin-right: 1px !important;
        }

        /* Modern Form Elements */
        .modern-form-group {
            margin-bottom: 16px !important;
        }

        .modern-form-label {
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 6px !important;
            display: block !important;
        }

        .req-star {
            color: #dc2626 !important;
            font-weight: 700 !important;
            margin-left: 2px !important;
        }

        .opt-tag {
            color: #94a3b8 !important;
            font-weight: 500 !important;
            font-size: 11.5px !important;
            margin-left: 4px !important;
        }

        .modern-input-box {
            position: relative !important;
            width: 100% !important;
            display: block !important;
        }

        .modern-input-box .modern-input-icon {
            position: absolute !important;
            left: 14px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #94a3b8 !important;
            font-size: 14px !important;
            pointer-events: none !important;
            z-index: 5 !important;
            line-height: 1 !important;
            margin: 0 !important;
        }

        .modern-input-box input.modern-input-control {
            width: 100% !important;
            height: 42px !important;
            line-height: 42px !important;
            padding: 0 14px 0 42px !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            outline: none !important;
            box-sizing: border-box !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        }

        .modern-input-box input.modern-input-control:focus {
            border-color: #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        }

        .modern-input-box input.modern-input-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        .modern-form-hint {
            font-size: 11.5px !important;
            color: #64748b !important;
            margin-top: 5px !important;
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            line-height: 1.4 !important;
        }

        .modern-form-hint i {
            color: #2563eb !important;
            font-size: 11px !important;
        }

        textarea.modern-textarea-control {
            width: 100% !important;
            padding: 10px 14px !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            outline: none !important;
            box-sizing: border-box !important;
            resize: vertical !important;
            min-height: 64px !important;
            max-height: 120px !important;
            line-height: 1.45 !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
        }

        textarea.modern-textarea-control:focus {
            border-color: #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        }

        textarea.modern-textarea-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }

        /* Modern Alert Messages */
        .modern-status-alert {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            padding: 10px 14px !important;
            margin-bottom: 12px !important;
            display: none;
            align-items: center !important;
            gap: 8px !important;
            line-height: 1.4 !important;
        }

        .modern-status-alert.status-info {
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            color: #1d4ed8 !important;
        }

        .modern-status-alert.status-success {
            background: #f0fdf4 !important;
            border: 1px solid #bbf7d0 !important;
            color: #15803d !important;
        }

        .modern-status-alert.status-error {
            background: #fef2f2 !important;
            border: 1px solid #fecaca !important;
            color: #b91c1c !important;
        }

        /* Modern Modal Footer */
        .modern-reminder-footer {
            background: #ffffff !important;
            border-top: 1px solid #f1f5f9 !important;
            padding: 14px 24px !important;
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .modern-btn-cancel {
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            border-radius: 9px !important;
            padding: 8px 18px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .modern-btn-cancel:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }

        .modern-btn-send {
            background: linear-gradient(135deg, #0d4d8b 0%, #1e40af 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            border-radius: 9px !important;
            padding: 9px 22px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            cursor: pointer !important;
            box-shadow: 0 4px 14px rgba(13, 77, 139, 0.25) !important;
            transition: all 0.2s ease !important;
        }

        .modern-btn-send:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35) !important;
        }

        .modern-btn-send:active {
            transform: translateY(0) !important;
        }

        .modern-btn-send:disabled {
            opacity: 0.65 !important;
            cursor: not-allowed !important;
            transform: none !important;
        }

        /* Dark Mode Overrides for Modern Due Reminder Modal */
        html.theme-dark #duePaymentReminderModal .modal-content {
            background: var(--pp-surface) !important;
            border: 1px solid var(--pp-border) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7) !important;
        }

        html.theme-dark .modern-reminder-header {
            background: var(--pp-surface) !important;
            border-bottom: 1px solid var(--pp-border) !important;
        }

        html.theme-dark .modern-header-icon {
            background: rgba(56, 189, 248, 0.15) !important;
            color: #38bdf8 !important;
            box-shadow: none !important;
        }

        html.theme-dark .modern-header-title {
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-header-subtitle {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .modern-header-close {
            background: var(--pp-surface-2) !important;
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .modern-header-close:hover {
            background: var(--pp-border) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-reminder-body {
            background: var(--pp-bg) !important;
        }

        html.theme-dark .modern-summary-card {
            background: var(--pp-surface) !important;
            border-color: var(--pp-border) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3) !important;
        }

        html.theme-dark .summary-label {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .summary-client-name {
            color: var(--pp-text) !important;
        }

        html.theme-dark .summary-meta-row {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .summary-meta-item i {
            color: #60a5fa !important;
        }

        html.theme-dark .summary-balance-section {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12) 0%, rgba(185, 28, 28, 0.12) 100%) !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
        }

        html.theme-dark .balance-title {
            color: #f87171 !important;
        }

        html.theme-dark .balance-subtitle {
            color: #fca5a5 !important;
        }

        html.theme-dark .balance-amount-display {
            color: #f87171 !important;
        }

        html.theme-dark .modern-form-label {
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-input-box input.modern-input-control,
        html.theme-dark textarea.modern-textarea-control {
            background-color: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-input-box input.modern-input-control:focus,
        html.theme-dark textarea.modern-textarea-control:focus {
            border-color: var(--pp-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.2) !important;
        }

        html.theme-dark .modern-input-box .modern-input-icon {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .modern-form-hint {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .modern-reminder-footer {
            background: var(--pp-surface) !important;
            border-top: 1px solid var(--pp-border) !important;
        }

        html.theme-dark .modern-btn-cancel {
            background: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-btn-cancel:hover {
            background: var(--pp-border) !important;
            color: #ffffff !important;
        }

        html.theme-dark .modern-btn-send {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%) !important;
        }
    </style>

    <div id="page-content-wrapper">

        <div class="page-content inset">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="invoice-section-title"><i class="fa fa-money"></i> Due Payments & Follow-Up</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 main_content" style="">
                        <div class="invoice-filter-panel">
                            <div class="invoice-filter-item search-item">
                                <i class="fa fa-search"></i>
                                <input type="text" id="paymentClientSearch" placeholder="Search by Client Name...">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="number" id="minAmount" placeholder="Min Amount">
                            </div>
                            <div class="invoice-filter-item">
                                <input type="number" id="maxAmount" placeholder="Max Amount">
                            </div>
                            <div class="invoice-filter-item" style="flex: 0 0 160px;">
                                <button type="button" class="btn-report-export btn-solid-primary downloadPaymentReport"
                                    style="width: 100%;">
                                    <i class="fa fa-cloud-download" style="font-size: 15px;"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12 main_content table-responsive">
                        <table class="table table-hover table-sortable table-striped" id="tab_customer_list"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="">
                                        <small><a>Client Name</a></small>
                                        <a><a>Contact Person Name</a></a>
                                        <script>document.getElementById('contactPersonName').value = '{contactPersonName}';</script>
                                    </th>
                                    <th class="">
                                        <small><a>Contact Person</a></small>

                                    </th>
                                    <th class="">
                                        <small><a>Phone Number</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Invoice Header</a></small>
                                    </th>
                                    
   <th class="">
                                        <small><a>Invoices Issued</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Amount Received</a></small>
                                    </th>

                                     <th class="">
                                        <small><a>Amount pending</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments (If Any)</a></small>
                                    </th>
                                    <th class="text-center" style="width: 70px;">
                                        <small><a>Mail</a></small>
                                        <a href="">Count</a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPaymentInfo as $paymentInfo) { ?>
                                    <tr id='addr<?php echo $paymentInfo['id'] ?>'>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo htmlspecialchars($paymentInfo['customer_name']) ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo htmlspecialchars($paymentInfo['contact_person'] ?? '') ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo htmlspecialchars($paymentInfo['contact'] ?? '') ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo htmlspecialchars($paymentInfo['service_type'] ?? '') ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo number_format((float)$paymentInfo['balance'], 2, '.', '') ?></small>
                                        </td>
                                        <td data-name="sel"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <div class="table-edit-control">
                                                <textarea rows="2" class="saveChanges"
                                                    data-customer-id="<?php echo $paymentInfo['id'] ?>"
                                                    data-service-type="<?php echo htmlspecialchars($paymentInfo['service_type'] ?? '') ?>"
                                                    name="payComments" placeholder="Add follow-up notes..."><?php if (!empty($paymentInfo['comment'])) echo htmlspecialchars($paymentInfo['comment']); ?></textarea>
                                            </div>
                                        </td>
                                        <td class="text-center" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; vertical-align: middle;">
                                            <button type="button" class="btn btn-reminder-mail btnSendReminderMail"
                                                data-id="<?php echo $paymentInfo['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($paymentInfo['customer_name'], ENT_QUOTES); ?>"
                                                data-contact-person="<?php echo htmlspecialchars($paymentInfo['contact_person'] ?? '', ENT_QUOTES); ?>"
                                                data-phone="<?php echo htmlspecialchars($paymentInfo['contact'] ?? '', ENT_QUOTES); ?>"
                                                data-email="<?php echo htmlspecialchars($paymentInfo['email_id'] ?? '', ENT_QUOTES); ?>"
                                                data-service="<?php echo htmlspecialchars($paymentInfo['service_type'] ?? '', ENT_QUOTES); ?>"
                                                data-balance="<?php echo number_format((float)$paymentInfo['balance'], 2, '.', ''); ?>"
                                                title="Send Payment Reminder Email">
                                                <i class="fa fa-envelope"></i>
                                            </button>
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

<!-- Outstanding Due Payment Reminder Modal -->
<div id="duePaymentReminderModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Modern Clean Header -->
            <div class="modal-header modern-reminder-header">
                <div class="modern-header-left">
                    <div class="modern-header-icon">
                        <i class="fa fa-envelope-o"></i>
                    </div>
                    <div>
                        <h4 class="modern-header-title">Send Payment Reminder</h4>
                        <p class="modern-header-subtitle">Email client their balance statement &amp; bank details</p>
                    </div>
                </div>
                <button type="button" class="modern-header-close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <!-- Modern Modal Body -->
            <div class="modal-body modern-reminder-body">
                <!-- Executive Summary Card -->
                <div class="modern-summary-card">
                    <div class="summary-client-section">
                        <div>
                            <span class="summary-label">Client Account</span>
                            <h4 id="modalReminderClientName" class="summary-client-name">Client Name</h4>
                            <div class="summary-meta-row">
                                <span class="summary-meta-item">
                                    <i class="fa fa-user-circle-o"></i>
                                    <span id="modalReminderContactPerson">Accounts</span>
                                </span>
                                <span class="summary-meta-sep">•</span>
                                <span class="summary-meta-item">
                                    <i class="fa fa-phone"></i>
                                    <span id="modalReminderPhone">-</span>
                                </span>
                            </div>
                        </div>
                        <div class="overdue-badge">
                            <span class="overdue-pulse"></span> Payment Due
                        </div>
                    </div>

                    <!-- Highlighted Outstanding Balance Banner -->
                    <div class="summary-balance-section">
                        <div>
                            <div class="balance-title">
                                <i class="fa fa-clock-o"></i> Total Outstanding Due
                            </div>
                            <div class="balance-subtitle">Pending ledger balance as of today</div>
                        </div>
                        <div class="balance-amount-display">
                            <span class="balance-currency">₹</span><span id="modalReminderDueAmount">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Form Controls -->
                <form id="reminderMailForm" onsubmit="return false;">
                    <input type="hidden" id="modalReminderCustomerId" value="">
                    
                    <div class="modern-form-group" style="margin-bottom: 18px !important;">
                        <label class="modern-form-label" for="modalReminderEmail">
                            Recipient Email Address <span class="req-star">*</span>
                        </label>
                        <div class="modern-input-box">
                            <i class="fa fa-envelope-o modern-input-icon"></i>
                            <input type="text" id="modalReminderEmail" class="modern-input-control" placeholder="client@example.com" required>
                        </div>
                        <div class="modern-form-hint">
                            <i class="fa fa-info-circle"></i>
                            <span>Auto-fetched from client profile. Multiple comma-separated emails supported.</span>
                        </div>
                    </div>

                    <div id="modalReminderStatusMsg" class="modern-status-alert"></div>
                </form>
            </div>

            <!-- Modern Modal Footer -->
            <div class="modal-footer modern-reminder-footer">
                <button type="button" class="modern-btn-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmSendDueReminder" class="modern-btn-send">
                    <i class="fa fa-paper-plane"></i> Send Email
                </button>
            </div>
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

    $.fn.DataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var minVal = $('#minAmount').val();
            var maxVal = $('#maxAmount').val();
            var min = minVal !== '' ? parseFloat(minVal) : NaN;
            var max = maxVal !== '' ? parseFloat(maxVal) : NaN;
            var amountStr = data[4] || '';
            var amount = parseFloat(amountStr.replace(/[^0-9.-]+/g, '')) || 0;

            if ((isNaN(min) && isNaN(max)) ||
                (isNaN(min) && amount <= max) ||
                (min <= amount && isNaN(max)) ||
                (min <= amount && amount <= max)) {
                return true;
            }
            return false;
        }
    );

    $(document).ready(function () {

        var debouncedSearch = debounce(function () {
            table.search($('#paymentClientSearch').val()).draw();
        }, 200);

        $('#paymentClientSearch').on('input', debouncedSearch);

        $('#minAmount, #maxAmount').on('keyup change', function () {
            table.draw();
        });

        // Holds the report parameters chosen when a Download Report button is clicked.
        var pendingReport = null;
        // Format selected in step 1 ('pdf' | 'excel').
        var pendingFormat = null;

        function submitReportDownload() {
            if (!pendingReport || !pendingFormat) {
                return;
            }
            var url = (pendingFormat === 'excel') ? 'exportPaymentReportExcel.php' : 'exportPaymentReport.php';
            var fields = {
                minAmount: pendingReport.minAmount,
                maxAmount: pendingReport.maxAmount,
                clientName: pendingReport.clientName
            };
            var $form = $('<form>', {
                method: 'post',
                action: url
            }).css('display', 'none');

            $.each(fields, function (name, value) {
                $('<input>', {
                    type: 'hidden',
                    name: name,
                    value: value || ''
                }).appendTo($form);
            });

            $('body').append($form);
            $form[0].submit();
            setTimeout(function () { $form.remove(); }, 1000);
            closeFormatChooser();
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

            $.post('emailPaymentReport.php', {
                minAmount: pendingReport.minAmount,
                maxAmount: pendingReport.maxAmount,
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

        $('#report-format-chooser').click(function (e) {
            if (e.target === this) {
                closeFormatChooser();
            }
        });

        $('.downloadPaymentReport').click(function () {
            var minAmount = $('#minAmount').val();
            var maxAmount = $('#maxAmount').val();
            var clientName = $('#paymentClientSearch').val();
            
            openFormatChooser({
                minAmount: minAmount,
                maxAmount: maxAmount,
                clientName: clientName
            });
        });

        $('.saveChanges').on('change blur', function (e) {
            saveData($(this));
        });

        function saveData($selector) {
            var checkedValue = $selector.val();
            var customerId = $selector.attr('data-customer-id');
            var serviceType = $selector.attr('data-service-type');
            if (!customerId) return;

            $.ajax({
                url: "addComments.php",
                type: "POST",
                data: {
                    action: 'savePaymentFollowUp',
                    customerId: customerId,
                    serviceType: serviceType,
                    comment: checkedValue
                },
                success: function (data) {
                    $selector.css('border-color', '#10b981');
                    setTimeout(function () {
                        $selector.css('border-color', '');
                    }, 1500);
                },
                error: function (xhr, status, error) {
                    $selector.css('border-color', '#ef4444');
                }
            });

            $selector.removeClass("glowing-border");
            $selector.addClass("lookslike-label");
        }

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

        // Open Send Due Reminder Modal
        $('#tab_customer_list').on('click', '.btnSendReminderMail', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var id = $btn.data('id');
            var name = $btn.data('name');
            var contactPerson = $btn.data('contact-person') || 'Accounts';
            var phone = $btn.data('phone') || '-';
            var email = $btn.data('email') || '';
            var balance = $btn.data('balance') || '0.00';

            $('#modalReminderCustomerId').val(id);
            $('#modalReminderClientName').text(name);
            $('#modalReminderContactPerson').text(contactPerson);
            $('#modalReminderPhone').text(phone);
            // Service / months badge is excluded from display ("ye mat karo mention")
            $('#modalReminderDueAmount').text(parseFloat(balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#modalReminderEmail').val(email);
            $('#modalReminderStatusMsg').removeClass('status-info status-success status-error').hide().empty();
            $('#btnConfirmSendDueReminder').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');

            $('#duePaymentReminderModal').modal('show');
        });

        // Submit Send Due Reminder Email
        $('#btnConfirmSendDueReminder').click(function () {
            var customerId = $('#modalReminderCustomerId').val();
            var recipientEmail = $.trim($('#modalReminderEmail').val());
            var $msg = $('#modalReminderStatusMsg');
            var $btn = $(this);

            if (!recipientEmail) {
                $msg.removeClass('status-info status-success').addClass('status-error')
                    .html('<i class="fa fa-exclamation-circle"></i> Please enter a recipient email address.').show();
                $('#modalReminderEmail').focus();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.removeClass('status-success status-error').addClass('status-info')
                .html('<i class="fa fa-spinner fa-spin"></i> Sending payment reminder email, please wait...').show();

            $.ajax({
                url: 'sendPaymentDueReminder.php',
                type: 'POST',
                data: {
                    customerId: customerId,
                    recipientEmail: recipientEmail
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        $msg.removeClass('status-info status-error').addClass('status-success')
                            .html('<i class="fa fa-check-circle"></i> ' + (res.message || 'Email sent successfully!')).show();
                        $btn.html('<i class="fa fa-check"></i> Sent');

                        // Update follow-up comment in the table row if logged
                        if (res.loggedNote) {
                            var $rowTextarea = $('#addr' + customerId).find('.saveChanges');
                            var currentVal = $rowTextarea.val();
                            var newVal = currentVal ? currentVal + '\n' + res.loggedNote : res.loggedNote;
                            $rowTextarea.val(newVal);
                        }

                        setTimeout(function () {
                            $('#duePaymentReminderModal').modal('hide');
                        }, 1800);
                    } else {
                        $msg.removeClass('status-info status-success').addClass('status-error')
                            .html('<i class="fa fa-exclamation-triangle"></i> ' + (res.message || 'Failed to send email.')).show();
                        $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
                    }
                },
                error: function (xhr, status, error) {
                    $msg.removeClass('status-info status-success').addClass('status-error')
                        .html('<i class="fa fa-exclamation-triangle"></i> Server error while sending email. Please try again.').show();
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
                }
            });
        });

        $("#menu-toggle").click(function (e) {
            e.preventDefault();
            $("#wrapper").toggleClass("active");
            $(".container").toggleClass("active");
            table.draw();
        });

    });

    var table = $('#tab_customer_list').DataTable({
        //            "scrollY": "450px",
        "sScrollX": "100%",
        "scrollY": '70vh',
        "scrollCollapse": true,
        "paging": false,
        "ordering": false,
        "lengthChange": false,
        "paging": false,
        "columnDefs": [
            {
                "width": "20%",
                "targets": 0
            },
            {
                "width": "14%",
                "targets": 1
            },
            {
                "width": "12%",
                "targets": 2
            },
            {
                "width": "14%",
                "targets": 3
            },
            {
                "width": "12%",
                "targets": 4
            },
            {
                "width": "22%",
                "targets": 5
            },
            {
                "width": "6%",
                "targets": 6,
                "orderable": false,
                "searchable": false
            }
        ]
    });
    table.columns.adjust().draw();
</script>

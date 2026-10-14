<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/include/rbac_matrix.php';
if (!aitf_has_access("Payment Due")) {
    header("Location: homePage.php");
    echo "<script>window.location.href='homePage.php';</script>";
    exit;
}
?>
<div id="wrapper" class="active">
    <!-- Sidebar -->
    <!-- Sidebar -->

    <?php
    $pageIdentifier = "Finance Page";
    include 'header.php';
    include 'dbConnect.php';
    require_once __DIR__ . '/include/payment_due_helper.php';

    $allPaymentInfo = aitf_get_due_payments($db);
    $stmtFirm = $db->query("SELECT firm_name, firm_bank, bank_ac_no, bank_ifsc_code, firm_contact, firm_email FROM consulting_firm LIMIT 1");
    $firmInfo = $stmtFirm ? $stmtFirm->fetch(PDO::FETCH_ASSOC) : [];
    $firmBank = $firmInfo['firm_bank'] ?? 'Bank';
    $firmAc = $firmInfo['bank_ac_no'] ?? '';
    $firmIfsc = $firmInfo['bank_ifsc_code'] ?? '';
    $firmContact = !empty($firmInfo['firm_contact']) ? trim($firmInfo['firm_contact']) : '9422011836';
    $firmFormattedPhone = '+91 ' . preg_replace('/^(\d{5})(\d{5})$/', '$1 $2', $firmContact);
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
        .payment-page-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

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

        #tab_customer_list tbody td:first-child small .client-contact-person,
        .client-contact-person {
            display: inline !important;
            font-weight: 500 !important;
            color: #475569 !important;
            margin-left: 5px !important;
            font-size: 11.5px;
        }

        #tab_customer_list tbody td:first-child:hover small .client-contact-person {
            color: #0369a1 !important;
        }

        /* Service Type column badge */
        #tab_customer_list tbody td:nth-child(2) small {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }

        /* Invoices Issued */
        #tab_customer_list tbody td:nth-child(3) small {
            color: #1e293b;
            font-weight: 700;
            font-size: 12.5px;
        }

        /* Amount Received */
        #tab_customer_list tbody td:nth-child(4) small {
            color: #16a34a;
            font-weight: 700;
            font-size: 12.5px;
        }

        /* Payment Pending Amount */
        #tab_customer_list tbody td:nth-child(5) small {
            color: #dc2626;
            font-weight: 800;
            font-size: 13px;
        }

        /* Mail Count Badge (Interactive History Viewer) */
        .reminder-count-container {
            margin-top: 5px;
            font-size: 11px;
            font-weight: 700;
            color: #0d4d8b;
            text-align: center;
            line-height: 1.2;
            white-space: nowrap;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            transition: all 0.2s ease;
            user-select: none;
        }

        .reminder-count-container:hover {
            background: #0d4d8b;
            color: #ffffff;
            border-color: #0d4d8b;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(13, 77, 139, 0.25);
        }

        .reminder-count-container .reminder-count-val {
            font-weight: 800;
            color: #1e40af;
            background: #dbeafe;
            padding: 1px 6px;
            border-radius: 10px;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .reminder-count-container:hover .reminder-count-val {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
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
        html.theme-dark #tab_customer_list tbody td:first-child small .client-contact-person,
        html.theme-dark .client-contact-person { color: #94a3b8 !important; }
        html.theme-dark #tab_customer_list tbody td:first-child:hover small .client-contact-person { color: #7dd3fc !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(2) small { background: rgba(56, 189, 248, 0.15); color: #7dd3fc; }
        html.theme-dark #tab_customer_list tbody td:nth-child(3) small { color: #e2e8f0 !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(4) small { color: #4ade80 !important; }
        html.theme-dark #tab_customer_list tbody td:nth-child(5) small { color: #f87171 !important; }
        html.theme-dark .reminder-count-container { color: #94a3b8; }
        html.theme-dark .reminder-count-container .reminder-count-val {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
        }

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



        /* =====================================================================
           ULTRA-MODERN DUE PAYMENT REMINDER MODAL
           ===================================================================== */
        #duePaymentReminderModal {
            overflow-y: auto !important;
            padding-right: 0 !important;
        }

        #duePaymentReminderModal .modal-dialog {
            max-width: 740px !important;
            width: 95% !important;
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

        /* Mail Body Editor & Preview Styles */
        .mail-body-tab-pills {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
        }

        .btn-mail-tab {
            padding: 4px 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-mail-tab:hover {
            color: #0f172a;
        }

        .btn-mail-tab.active {
            background: #0d4d8b;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(13, 77, 139, 0.2);
        }

        .btn-mail-reset {
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px dashed #ef4444;
            background: #fef2f2;
            color: #dc2626;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
        }

        .btn-mail-reset:hover {
            background: #fee2e2;
        }

        .mail-body-editor-wrapper {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.2s ease;
        }

        .mail-body-editor-wrapper:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .mail-preview-pane {
            height: 250px;
            overflow-y: auto;
            padding: 14px 16px;
            font-size: 13px;
            line-height: 1.6;
            color: #1e293b;
            background: #ffffff;
        }

        .modern-body-editor {
            width: 100% !important;
            height: 200px !important;
            box-sizing: border-box !important;
            padding: 12px 14px !important;
            font-size: 12.5px !important;
            line-height: 1.55 !important;
            border: none !important;
            outline: none !important;
            resize: vertical !important;
            color: #0f172a !important;
            background: #ffffff !important;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif !important;
        }

        .modern-body-editor-half {
            width: 100% !important;
            min-height: 95px !important;
            box-sizing: border-box !important;
            padding: 10px 14px !important;
            font-size: 12.5px !important;
            line-height: 1.55 !important;
            border: none !important;
            outline: none !important;
            resize: vertical !important;
            color: #0f172a !important;
            background: #ffffff !important;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif !important;
        }

        /* Non-Editable Locked Bank Details Card in Editor */
        .non-editable-bank-wrapper {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 8px;
            padding: 10px 14px;
            margin: 8px 10px;
            user-select: none;
        }

        .non-editable-bank-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 6px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #bbf7d0;
        }

        .bank-lock-badge {
            font-size: 11px;
            font-weight: 800;
            color: #166534;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .bank-lock-desc {
            font-size: 10px;
            font-weight: 600;
            color: #15803d;
            background: #dcfce7;
            padding: 2px 7px;
            border-radius: 4px;
        }

        .non-editable-bank-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 14px;
            font-size: 11.5px;
        }

        .bank-grid-item {
            display: flex;
            flex-direction: column;
        }

        .bank-grid-item label {
            font-size: 10px;
            font-weight: 700;
            color: #166534;
            text-transform: uppercase;
            margin: 0 0 1px 0;
            letter-spacing: 0.3px;
        }

        .bank-grid-item span {
            color: #0f172a;
            font-weight: 700;
            font-family: inherit;
        }

        .bank-ac-pill {
            color: #15803d !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
        }

        /* Attachment Section in Reminder Modal */
        .badge-attachment-count {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #e0f2fe;
            color: #0369a1;
            margin-left: 6px;
        }

        .btn-add-attachment {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #0d4d8b;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 11px;
            border-radius: 7px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .btn-add-attachment:hover {
            background: #0d4d8b;
            color: #ffffff;
            border-color: #0d4d8b;
        }

        .attachments-help-text {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.35;
        }

        .reminder-attachments-container {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 10px;
            max-height: 165px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .attachment-card-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            transition: background 0.15s ease;
        }

        .attachment-card-item:hover {
            background: #f1f5f9;
        }

        .attachment-card-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }

        .attachment-card-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .attachment-card-info {
            min-width: 0;
            flex: 1;
        }

        .attachment-card-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attachment-card-sub {
            font-size: 11px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .attachment-badge {
            font-size: 9.5px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-invoice {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-service-doc {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-manual {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-remove-attachment {
            background: transparent;
            border: none;
            color: #94a3b8;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-remove-attachment:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .attachments-empty-msg {
            padding: 14px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            font-style: italic;
        }

        .badge-auto-template {
            font-size: 10.5px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-custom-template {
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            display: inline-flex;
            align-items: center;
            gap: 4px;
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

        html.theme-dark .mail-body-tab-pills {
            background: var(--pp-surface-2) !important;
        }

        html.theme-dark .btn-mail-tab {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .btn-mail-tab:hover {
            color: #ffffff !important;
        }

        html.theme-dark .btn-mail-tab.active {
            background: var(--pp-accent) !important;
            color: #ffffff !important;
        }

        html.theme-dark .mail-body-editor-wrapper {
            border-color: var(--pp-border-2) !important;
            background: var(--pp-surface-2) !important;
        }

        html.theme-dark .mail-preview-pane {
            background: var(--pp-surface-2) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .modern-body-editor {
            background: var(--pp-surface-2) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .badge-auto-template {
            background: var(--pp-surface) !important;
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .modern-body-editor-half {
            background: var(--pp-surface-2) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark .badge-attachment-count {
            background: rgba(3, 105, 161, 0.25) !important;
            color: #38bdf8 !important;
        }

        html.theme-dark .btn-add-attachment {
            background: var(--pp-surface-2) !important;
            color: #60a5fa !important;
            border-color: var(--pp-border-2) !important;
        }

        html.theme-dark .btn-add-attachment:hover {
            background: #1d4ed8 !important;
            color: #ffffff !important;
        }

        html.theme-dark .reminder-attachments-container {
            background: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
        }

        html.theme-dark .attachment-card-item {
            background: var(--pp-surface) !important;
            border-color: var(--pp-border) !important;
        }

        html.theme-dark .attachment-card-item:hover {
            background: var(--pp-surface-2) !important;
        }

        html.theme-dark .attachment-card-title {
            color: var(--pp-text) !important;
        }

        html.theme-dark .non-editable-bank-wrapper {
            background: rgba(22, 101, 52, 0.15) !important;
            border-color: rgba(34, 197, 94, 0.35) !important;
        }

        html.theme-dark .non-editable-bank-banner {
            border-bottom-color: rgba(34, 197, 94, 0.2) !important;
        }

        html.theme-dark .bank-lock-badge {
            color: #4ade80 !important;
        }

        html.theme-dark .bank-lock-desc {
            background: rgba(34, 197, 94, 0.2) !important;
            color: #86efac !important;
        }

        html.theme-dark .bank-grid-item label {
            color: #86efac !important;
        }

        html.theme-dark .bank-grid-item span {
            color: var(--pp-text) !important;
        }

        html.theme-dark .bank-ac-pill {
            color: #4ade80 !important;
        }

        /* Modern CC Notice Box in Reminder Modal */
        .modern-cc-box {
            margin-top: 6px;
            padding: 7px 11px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: 11.5px;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 7px;
            line-height: 1.35;
        }

        .modern-cc-box i {
            font-size: 13px;
            color: #15803d;
            flex-shrink: 0;
        }

        .cc-tag {
            font-family: inherit;
            font-weight: 700;
            background: #dcfce7;
            color: #14532d;
            padding: 1px 5px;
            border-radius: 4px;
            border: 1px solid #86efac;
            font-size: 11px;
        }

        /* Reminder Email History Modal (Date & Time Log) */
        #dueReminderHistoryModal .modal-dialog {
            max-width: 620px !important;
            width: 95% !important;
            margin: 25px auto !important;
        }

        #dueReminderHistoryModal .modal-content {
            border-radius: 20px !important;
            overflow: hidden !important;
            border: none !important;
            box-shadow: 0 25px 60px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.08) !important;
            background: #ffffff !important;
        }

        .history-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .reminder-timeline-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .timeline-card-item {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            position: relative;
        }

        .timeline-card-item:hover {
            border-color: #93c5fd;
            box-shadow: 0 4px 12px rgba(13, 77, 139, 0.08);
        }

        .timeline-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .timeline-date-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .timeline-date-badge i {
            color: #0d4d8b;
            font-size: 13px;
        }

        .timeline-order-pill {
            font-size: 10.5px;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 7px;
            border-radius: 999px;
        }

        .timeline-detail-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px 14px;
            font-size: 12px;
            color: #475569;
            margin-bottom: 4px;
        }

        .timeline-detail-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .timeline-detail-item i {
            color: #64748b;
            font-size: 11px;
        }

        .timeline-email-val {
            font-weight: 700;
            color: #0f172a;
        }

        .timeline-cc-val {
            color: #166534;
            font-weight: 600;
            background: #f0fdf4;
            padding: 1px 6px;
            border-radius: 4px;
            border: 1px solid #bbf7d0;
            font-size: 11px;
        }

        .timeline-service-pill {
            font-size: 10.5px;
            font-weight: 700;
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .timeline-attachments-box {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid #f1f5f9;
            font-size: 11.5px;
        }

        .timeline-att-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 600;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 2px 8px;
            margin-right: 5px;
            margin-top: 4px;
        }

        /* Dark Mode Overrides for CC Notice & History Modal */
        html.theme-dark .modern-cc-box {
            background: rgba(22, 101, 52, 0.15) !important;
            border-color: rgba(34, 197, 94, 0.35) !important;
            color: #86efac !important;
        }

        html.theme-dark .modern-cc-box i {
            color: #4ade80 !important;
        }

        html.theme-dark .cc-tag {
            background: rgba(34, 197, 94, 0.2) !important;
            border-color: rgba(34, 197, 94, 0.4) !important;
            color: #bbf7d0 !important;
        }

        html.theme-dark #dueReminderHistoryModal .modal-content {
            background: var(--pp-surface) !important;
            border: 1px solid var(--pp-border) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7) !important;
        }

        html.theme-dark .timeline-card-item {
            background: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
        }

        html.theme-dark .timeline-card-item:hover {
            border-color: var(--pp-accent) !important;
        }

        html.theme-dark .timeline-card-header {
            border-bottom-color: var(--pp-border) !important;
        }

        html.theme-dark .timeline-date-badge {
            color: var(--pp-text) !important;
        }

        html.theme-dark .timeline-date-badge i {
            color: #38bdf8 !important;
        }

        html.theme-dark .timeline-order-pill {
            background: var(--pp-surface) !important;
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .timeline-detail-row {
            color: var(--pp-text-dim) !important;
        }

        html.theme-dark .timeline-email-val {
            color: var(--pp-text) !important;
        }

        html.theme-dark .timeline-cc-val {
            background: rgba(22, 101, 52, 0.2) !important;
            border-color: rgba(34, 197, 94, 0.3) !important;
            color: #86efac !important;
        }

        html.theme-dark .timeline-service-pill {
            background: rgba(56, 189, 248, 0.15) !important;
            color: #38bdf8 !important;
        }

        html.theme-dark .timeline-attachments-box {
            border-top-color: var(--pp-border) !important;
        }

        html.theme-dark .timeline-att-badge {
            background: var(--pp-surface) !important;
            border-color: var(--pp-border) !important;
            color: var(--pp-text) !important;
        }

        html.theme-dark #historyModalTimelineEmpty {
            background: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
        }

        html.theme-dark #historyModalTimelineEmpty h5 {
            color: var(--pp-text) !important;
        }

        html.theme-dark #historyModalTimelineEmpty p {
            color: var(--pp-text-dim) !important;
        }
    </style>

    <div id="page-content-wrapper">

        <div class="page-content inset">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 payment-page-heading">
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
                                    </th>
                                    <th class="">
                                        <small><a>Service Name</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Invoices Issued</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Amount Received</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Amount Pending</a></small>
                                    </th>
                                    <th class="text-center" style="width: 80px;">
                                        <small><a>Mail</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments (If any)</a></small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPaymentInfo as $paymentInfo) { ?>
                                    <tr id="addr<?php echo (int)$paymentInfo['id'] . '-' . md5($paymentInfo['header_key']); ?>" data-customer-id="<?php echo (int)$paymentInfo['id']; ?>" data-client-name="<?php echo htmlspecialchars($paymentInfo['customer_name'] . (!empty($paymentInfo['contact_person']) ? ' ' . $paymentInfo['contact_person'] : '') . (!empty($paymentInfo['contact']) ? ' ' . $paymentInfo['contact'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php
                                                echo htmlspecialchars($paymentInfo['customer_name']);
                                                $contactPerson = rtrim(trim((string)($paymentInfo['contact_person'] ?? '')), ",; -\t\n\r\0\x0B");
                                                $contactPhone = rtrim(trim((string)($paymentInfo['contact'] ?? '')), ",; -\t\n\r\0\x0B");
                                                $contactParts = [];
                                                if ($contactPerson !== '') {
                                                    $contactParts[] = htmlspecialchars($contactPerson);
                                                }
                                                if ($contactPhone !== '') {
                                                    $contactParts[] = htmlspecialchars($contactPhone);
                                                }
                                                if (!empty($contactParts)) {
                                                    echo ' <span class="client-contact-person" style="display: inline !important; font-weight: 500; color: #475569; margin-left: 5px;">(' . implode(' - ', $contactParts) . ')</span>';
                                                }
                                            ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo htmlspecialchars($paymentInfo['service_type'] ?? '') ?></small>
                                        </td>
                                        <td style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <?php foreach ($paymentInfo['invoices'] as $invoice) { ?>
                                                <small style="display:block;"><a class="payment-invoice-preview" href="finalInvoicePDF.php?invoiceid=<?php echo (int)$invoice['id']; ?>&amp;invoiceCustomerId=<?php echo (int)$paymentInfo['id']; ?>" data-number="<?php echo htmlspecialchars($invoice['number'], ENT_QUOTES, 'UTF-8'); ?>" title="Open invoice"><?php echo htmlspecialchars($invoice['number'], ENT_QUOTES, 'UTF-8'); ?></a></small>
                                            <?php } ?>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo number_format((float)($paymentInfo['total_paid'] ?? 0), 2, '.', '') ?></small>
                                        </td>
                                        <td class="customerAccount" data-id="<?php echo $paymentInfo['id'] ?>"
                                            data-name="name"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; border-left: 1px solid #ffffff">
                                            <small><?php echo number_format((float)$paymentInfo['balance'], 2, '.', '') ?></small>
                                        </td>
                                        <td class="text-center" style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff; vertical-align: middle;">
                                            <button type="button" class="btn btn-reminder-mail btnSendReminderMail"
                                                data-id="<?php echo $paymentInfo['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($paymentInfo['customer_name'], ENT_QUOTES); ?>"
                                                data-contact-person="<?php echo htmlspecialchars($paymentInfo['contact_person'] ?? '', ENT_QUOTES); ?>"
                                                data-phone="<?php echo htmlspecialchars($paymentInfo['contact'] ?? '', ENT_QUOTES); ?>"
                                                data-email="<?php echo htmlspecialchars($paymentInfo['email_id'] ?? '', ENT_QUOTES); ?>"
                                                data-service="<?php echo htmlspecialchars($paymentInfo['service_type'] ?? '', ENT_QUOTES); ?>"
                                                data-header-key="<?php echo htmlspecialchars($paymentInfo['header_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-balance="<?php echo number_format((float)$paymentInfo['balance'], 2, '.', ''); ?>"
                                                data-total-inv="<?php echo number_format((float)($paymentInfo['total_inv'] ?? 0), 2, '.', ''); ?>"
                                                data-total-paid="<?php echo number_format((float)($paymentInfo['total_paid'] ?? 0), 2, '.', ''); ?>"
                                                data-total-tds="<?php echo number_format((float)($paymentInfo['total_tds'] ?? 0), 2, '.', ''); ?>"
                                                data-invoices="<?php echo htmlspecialchars(json_encode($paymentInfo['invoices'] ?? []), ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Send Payment Reminder Email">
                                                <i class="fa fa-envelope"></i>
                                            </button>
                                            <div class="reminder-count-container btnViewReminderHistory"
                                                data-customer-id="<?php echo $paymentInfo['id']; ?>"
                                                data-customer-name="<?php echo htmlspecialchars($paymentInfo['customer_name'], ENT_QUOTES); ?>"
                                                data-header-key="<?php echo htmlspecialchars($paymentInfo['header_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-service="<?php echo htmlspecialchars($paymentInfo['service_type'] ?? '', ENT_QUOTES); ?>"
                                                data-count="<?php echo (int)($paymentInfo['reminder_count'] ?? 0); ?>"
                                                title="Click to view reminder email history (Date &amp; Time)">
                                                Count: <span class="reminder-count-val"><?php echo (int)($paymentInfo['reminder_count'] ?? 0); ?></span>
                                            </div>
                                        </td>
                                        <td data-name="sel"
                                            style="border-top: 1px solid #ffffff; border-right: 1px solid #ffffff;">
                                            <div class="table-edit-control">
                                                <textarea rows="2" class="saveChanges"
                                                    data-customer-id="<?php echo $paymentInfo['id'] ?>"
                                                    data-service-type="<?php echo htmlspecialchars($paymentInfo['header_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    name="payComments" placeholder="Add follow-up notes..."><?php if (!empty($paymentInfo['comment'])) echo htmlspecialchars($paymentInfo['comment']); ?></textarea>
                                            </div>
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

<div id="paymentInvoicePreviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="paymentInvoicePreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width:95%;max-width:1100px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="paymentInvoicePreviewTitle">Invoice</h4>
            </div>
            <div class="modal-body" style="padding:0;">
                <iframe id="paymentInvoicePreviewFrame" title="Invoice PDF" style="width:100%;height:75vh;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="paymentInvoicePreviewLink" class="btn btn-default" target="_blank" rel="noopener">Open PDF in new tab</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Outstanding Due Payment Reminder Modal -->
<div id="duePaymentReminderModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog modal-lg" style="max-width: 760px;">
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
                    <input type="hidden" id="modalReminderIsCustom" value="0">
                    
                    <div class="modern-form-group" style="margin-bottom: 12px !important;">
                        <label class="modern-form-label" for="modalReminderEmail">
                            Recipient Email Address <span class="req-star">*</span>
                        </label>
                        <div class="modern-input-box">
                            <i class="fa fa-envelope-o modern-input-icon"></i>
                            <input type="text" id="modalReminderEmail" class="modern-input-control" placeholder="client@example.com" required>
                        </div>
                        <div class="modern-cc-box">
                            <i class="fa fa-info-circle text-primary"></i>
                            <span><strong>CC (Auto-included):</strong> <code class="cc-tag">aitfmcl@gmail.com</code>, <code class="cc-tag">aitfacct@gmail.com</code> (Management &amp; Accounts)</span>
                        </div>
                    </div>

                    <div class="modern-form-group" style="margin-bottom: 12px !important;">
                        <label class="modern-form-label" for="modalReminderSubject">
                            Email Subject <span class="req-star">*</span>
                        </label>
                        <div class="modern-input-box">
                            <i class="fa fa-pencil modern-input-icon"></i>
                            <input type="text" id="modalReminderSubject" class="modern-input-control" placeholder="Outstanding Payment Reminder..." required>
                        </div>
                    </div>

                    <div class="modern-form-group" style="margin-bottom: 14px !important;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="modern-form-label" style="margin-bottom: 0;">
                                Email Body / Message <span class="req-star">*</span>
                            </label>
                            <div class="mail-body-tab-pills">
                                <button type="button" class="btn-mail-tab active" id="tabBtnPreview"><i class="fa fa-eye"></i> Mail Preview</button>
                                <button type="button" class="btn-mail-tab" id="tabBtnEdit"><i class="fa fa-pencil"></i> Edit Body</button>
                                <button type="button" class="btn-mail-reset" id="btnResetMailBody" title="Reset to auto-generated template" style="display:none;"><i class="fa fa-undo"></i> Reset</button>
                            </div>
                        </div>

                        <div class="mail-body-editor-wrapper">
                            <!-- Live Formatted Preview Card -->
                            <div id="modalReminderBodyPreview" class="mail-preview-pane"></div>

                            <!-- Editable Textareas with Non-Editable Bank Details in Middle -->
                            <div id="modalReminderBodyEditor" style="display: none;">
                                <textarea id="modalReminderBodyIntro" class="modern-body-editor-half" rows="4" placeholder="Message above bank details (Salutation, statement details, payment notice)..."></textarea>
                                
                                <!-- Locked Non-Editable Bank Details Card -->
                                <div class="non-editable-bank-wrapper" id="modalReminderBankLockedBox">
                                    <div class="non-editable-bank-banner">
                                        <span class="bank-lock-badge"><i class="fa fa-lock"></i> Company Bank Details (Locked / Non-Editable)</span>
                                        <span class="bank-lock-desc">Protected against changes</span>
                                    </div>
                                    <div class="non-editable-bank-grid">
                                        <div class="bank-grid-item"><label>Bank:</label> <span>IDBI Bank Ltd</span></div>
                                        <div class="bank-grid-item"><label>Account No:</label> <span class="bank-ac-pill">0087102000027849</span></div>
                                        <div class="bank-grid-item"><label>IFSC Code:</label> <span>IBKL0000087</span></div>
                                        <div class="bank-grid-item"><label>Beneficiary Name:</label> <span>AITF Management Certification Limited</span></div>
                                    </div>
                                </div>

                                <textarea id="modalReminderBodyClosing" class="modern-body-editor-half" rows="4" placeholder="Message below bank details (UTR confirmation request, queries note, signature)..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Email Attachments Section -->
                    <div class="modern-form-group" style="margin-bottom: 14px !important;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="modern-form-label" style="margin-bottom: 0;">
                                <i class="fa fa-paperclip" style="color: #0d4d8b;"></i> Attachments
                                <span id="reminderAttachmentCountBadge" class="badge-attachment-count">0 files</span>
                            </label>
                            <div>
                                <button type="button" class="btn-add-attachment" id="btnTriggerAddAttachment" title="Add attachment from your phone or PC">
                                    <i class="fa fa-paperclip"></i> Add Attachment
                                </button>
                                <input type="file" id="reminderManualFileInput" multiple style="display:none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                            </div>
                        </div>
                        <div class="attachments-help-text">
                            <i class="fa fa-info-circle"></i> Service invoice attachments are fetched automatically by default. You can delete any attachment or add more files from your device.
                        </div>
                        <div id="reminderAttachmentsContainer" class="reminder-attachments-container">
                            <!-- Populated dynamically -->
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

<!-- Outstanding Payment Reminder History Modal (Date & Time Log) -->
<div id="dueReminderHistoryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 10520;">
    <div class="modal-dialog" style="max-width: 640px; width: 95%;">
        <div class="modal-content">
            <!-- Modern Header -->
            <div class="modal-header modern-reminder-header">
                <div class="modern-header-left">
                    <div class="modern-header-icon" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important; color: #b45309 !important;">
                        <i class="fa fa-history"></i>
                    </div>
                    <div>
                        <h4 class="modern-header-title">Reminder Email History</h4>
                        <p class="modern-header-subtitle">Sent emails log with exact date, time, and recipients</p>
                    </div>
                </div>
                <button type="button" class="modern-header-close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body modern-reminder-body" style="max-height: 72vh; overflow-y: auto;">
                <!-- Client Info Card -->
                <div class="modern-summary-card" style="padding: 12px 16px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span class="summary-label" style="margin-bottom: 2px;">Client Account</span>
                            <strong id="historyModalClientName" style="font-size: 15px; color: #0f172a; display: block;">Client Name</strong>
                            <span id="historyModalServiceInfo" style="font-size: 11.5px; color: #64748b; font-weight: 500;">-</span>
                        </div>
                        <div class="history-count-badge">
                            <i class="fa fa-paper-plane text-primary"></i> Total Sent: <span id="historyModalTotalCount" style="font-weight: 800;">0</span>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="historyModalTimelineLoading" style="text-align: center; padding: 25px 10px; color: #64748b; font-size: 13px;">
                    <i class="fa fa-spinner fa-spin fa-2x" style="color: #0d4d8b; margin-bottom: 8px; display: block;"></i> Loading reminder history...
                </div>

                <!-- Empty State -->
                <div id="historyModalTimelineEmpty" style="display: none; text-align: center; padding: 30px 15px; background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 12px;">
                    <i class="fa fa-calendar-times-o" style="font-size: 34px; color: #94a3b8; margin-bottom: 10px; display: block;"></i>
                    <h5 style="margin: 0 0 4px 0; font-weight: 700; color: #334155;">No reminder emails logged</h5>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">No reminder email history has been recorded for this client yet.</p>
                </div>

                <!-- Populated Timeline List -->
                <div id="historyModalTimelineList" class="reminder-timeline-list" style="display: none;">
                    <!-- Rendered dynamically -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer modern-reminder-footer">
                <button type="button" class="modern-btn-cancel" data-dismiss="modal">Close</button>
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
            if (settings.nTable.id !== 'tab_customer_list') return true;
            var clientQuery = $.trim($('#paymentClientSearch').val() || '').toLocaleLowerCase();
            var clientName = $(settings.aoData[dataIndex].nTr).attr('data-client-name') || '';
            if (clientQuery && clientName.toLocaleLowerCase().indexOf(clientQuery) === -1) return false;
            return true;
        }
    );

    $(document).ready(function () {

        $('#tab_customer_list').on('click', '.payment-invoice-preview', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('#paymentInvoicePreviewTitle').text($(this).attr('data-number'));
            $('#paymentInvoicePreviewFrame').attr('src', this.href);
            $('#paymentInvoicePreviewLink').attr('href', this.href);
            $('#paymentInvoicePreviewModal').modal('show');
        });
        $('#paymentInvoicePreviewModal').on('hidden.bs.modal', function () {
            $('#paymentInvoicePreviewFrame').removeAttr('src');
        });

        var debouncedSearch = debounce(function () {
            table.draw();
        }, 200);

        $('#paymentClientSearch').on('input', debouncedSearch);

        

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
                    // Follow-up notes are stored per customer; keep repeated service rows in sync.
                    $('#tab_customer_list .saveChanges[data-customer-id="' + customerId + '"]').val(checkedValue);
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

        // Locked Non-Editable Bank Details Config (Protected against modification)
        var firmBankConfig = {
            bankName: 'IDBI Bank Ltd',
            accountNo: '0087102000027849',
            ifscCode: 'IBKL0000087',
            beneficiary: 'AITF Management Certification Limited'
        };
        var firmPhone = '<?php echo htmlspecialchars($firmFormattedPhone, ENT_QUOTES); ?>';
        var activeReminderData = null;
        var activeAttachments = [];
        var defaultGeneratedIntro = '';
        var defaultGeneratedClosing = '';
        var defaultGeneratedSubject = '';

        function escapeMailHtml(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
        }

        function updateMailPreview() {
            if (!activeReminderData) return;
            var isCustom = $('#modalReminderIsCustom').val() === '1';
            var introText = $('#modalReminderBodyIntro').val();
            var closingText = $('#modalReminderBodyClosing').val();

            var previewIntroHtml = '';
            var previewClosingHtml = '';

            if (isCustom) {
                previewIntroHtml = escapeMailHtml(introText).replace(/\n/g, '<br>');
                previewClosingHtml = escapeMailHtml(closingText).replace(/\n/g, '<br>');
            } else {
                var clientName = escapeMailHtml(activeReminderData.name);
                var contactPerson = escapeMailHtml(activeReminderData.contactPerson);
                var salutation = contactPerson || clientName;
                var serviceType = escapeMailHtml(activeReminderData.service);
                var dueAmount = activeReminderData.fmtBalance;
                var totalInv = activeReminderData.fmtInv;
                var totalPaid = activeReminderData.fmtPaid;
                var totalTds = activeReminderData.fmtTds;

                previewIntroHtml = '<div style="margin-bottom: 10px;">Dear <strong>' + salutation + '</strong> (' + clientName + '),</div>'
                    + '<p style="margin: 0 0 8px;">Greetings from <strong>AITF MANAGEMENT CERTIFICATION LIMITED</strong>.</p>'
                    + '<p style="margin: 0 0 12px;">We hope this email finds you well. This is a gentle reminder regarding the outstanding payment balance pending in your account.</p>'
                    + '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #0d4d8b; border-radius: 6px; padding: 10px 14px; margin: 12px 0;">'
                    + '<div style="font-size: 12px; font-weight: 700; color: #0d4d8b; margin-bottom: 6px; text-transform: uppercase;">Account &amp; Payment Statement:</div>'
                    + '<table style="width: 100%; border-collapse: collapse; font-size: 12px;">'
                    + '<tr><td style="padding: 3px 0; color: #64748b; width: 45%;">Client Name:</td><td style="padding: 3px 0; font-weight: 600; color: #0f172a;">' + clientName + '</td></tr>'
                    + '<tr><td style="padding: 3px 0; color: #64748b;">Service Name:</td><td style="padding: 3px 0; font-weight: 600; color: #0f172a;">' + serviceType + '</td></tr>'
                    + '<tr><td style="padding: 3px 0; color: #64748b;">Total Invoiced:</td><td style="padding: 3px 0; font-weight: 600; color: #0f172a;">₹' + totalInv + '</td></tr>'
                    + '<tr><td style="padding: 3px 0; color: #64748b;">Payment Received:</td><td style="padding: 3px 0; font-weight: 600; color: #0f172a;">₹' + totalPaid + '</td></tr>'
                    + (activeReminderData.totalTds > 0 ? '<tr><td style="padding: 3px 0; color: #64748b;">TDS Deducted:</td><td style="padding: 3px 0; font-weight: 600; color: #0f172a;">₹' + totalTds + '</td></tr>' : '')
                    + '<tr><td style="padding: 5px 0 2px 0; font-weight: 700; color: #dc2626;">Outstanding Due:</td><td style="padding: 5px 0 2px 0; font-weight: 800; font-size: 13.5px; color: #dc2626;">₹' + dueAmount + '/-</td></tr>'
                    + '</table>'
                    + '</div>'
                    + '<p style="margin: 0 0 10px;">As per our accounting records, the total outstanding balance of <strong style="color: #dc2626;">₹' + dueAmount + '/-</strong> is currently overdue. Kindly clear the outstanding balance at your earliest convenience.</p>'
                    + '<p style="margin: 0 0 4px;">You may transfer the amount via NEFT / RTGS / IMPS to our company account details below:</p>';

                previewClosingHtml = '<p style="margin: 10px 0 10px; font-size: 12px; color: #475569;">If payment has already been initiated, please share the transaction confirmation / UTR details to reconcile your ledger.</p>'
                    + '<p style="margin: 0 0 10px; font-size: 12px; color: #475569;">Should you have any queries or require copies of the invoices, please feel free to contact us.</p>'
                    + '<div style="margin-top: 14px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #475569;">'
                    + '<strong>Best Regards,</strong><br>'
                    + 'Accounts &amp; Finance Team<br>'
                    + '<span style="color: #0d4d8b; font-weight: 700;">AITF MANAGEMENT CERTIFICATION LIMITED</span><br>'
                    + '<span>Phone: ' + escapeMailHtml(firmPhone) + '</span><br>'
                    + '<span>Email: aitfacct@gmail.com</span>'
                    + '</div>';
            }

            var bankCardHtml = '<div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 10px 14px; margin: 12px 0; font-size: 12px; color: #166534;">'
                + '<strong style="color: #14532d; display: block; margin-bottom: 4px;"><i class="fa fa-university"></i> Company Bank Details:</strong>'
                + '<div>Bank: <b>' + escapeMailHtml(firmBankConfig.bankName) + '</b></div>'
                + '<div>Account No: <b>' + escapeMailHtml(firmBankConfig.accountNo) + '</b></div>'
                + '<div>IFSC Code: <b>' + escapeMailHtml(firmBankConfig.ifscCode) + '</b></div>'
                + '<div>Beneficiary: <b>' + escapeMailHtml(firmBankConfig.beneficiary) + '</b></div>'
                + '</div>';

            var attachmentsPreviewHtml = '';
            if (activeAttachments.length > 0) {
                var attItems = [];
                activeAttachments.forEach(function (att) {
                    attItems.push('<span style="display: inline-flex; align-items: center; gap: 4px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px 7px; margin: 2px 4px 2px 0; font-size: 11px; color: #0f172a;"><i class="fa fa-paperclip text-muted"></i> ' + escapeMailHtml(att.name) + '</span>');
                });
                attachmentsPreviewHtml = '<div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 11.5px; color: #475569;">'
                    + '<strong><i class="fa fa-paperclip"></i> Attached (' + activeAttachments.length + '):</strong><br>'
                    + '<div style="margin-top: 4px; display: flex; flex-wrap: wrap;">' + attItems.join('') + '</div>'
                    + '</div>';
            }

            var fullPreviewHtml = '<div style="font-size: 13px; line-height: 1.6; color: #1e293b;">'
                + previewIntroHtml
                + bankCardHtml
                + previewClosingHtml
                + attachmentsPreviewHtml
                + '</div>';

            $('#modalReminderBodyPreview').html(fullPreviewHtml);
        }

        function renderReminderAttachments() {
            var $c = $('#reminderAttachmentsContainer');
            $c.empty();

            var count = activeAttachments.length;
            $('#reminderAttachmentCountBadge').text(count + ' file' + (count === 1 ? '' : 's'));

            if (count === 0) {
                $c.html('<div class="attachments-empty-msg"><i class="fa fa-paperclip"></i> No attachments selected. (Click "+ Add Attachment" to add files from your device)</div>');
                updateMailPreview();
                return;
            }

            activeAttachments.forEach(function (att, idx) {
                var iconClass = 'fa-file-pdf-o text-danger';
                if (att.type === 'manual') {
                    var ext = (att.name.split('.').pop() || '').toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
                        iconClass = 'fa-file-image-o text-success';
                    } else if (['xls', 'xlsx', 'csv'].indexOf(ext) !== -1) {
                        iconClass = 'fa-file-excel-o text-success';
                    } else if (['doc', 'docx'].indexOf(ext) !== -1) {
                        iconClass = 'fa-file-word-o text-primary';
                    } else if (ext === 'pdf') {
                        iconClass = 'fa-file-pdf-o text-danger';
                    } else {
                        iconClass = 'fa-file-text-o text-secondary';
                    }
                } else if (att.type === 'service_doc') {
                    iconClass = 'fa-file-text-o text-primary';
                }

                var badgeClass = 'badge-invoice';
                var badgeText = 'Service Invoice';
                if (att.type === 'service_doc') {
                    badgeClass = 'badge-service-doc';
                    badgeText = 'Service Doc';
                } else if (att.type === 'manual') {
                    badgeClass = 'badge-manual';
                    badgeText = 'Manual Upload';
                }

                var $item = $('<div class="attachment-card-item"></div>');
                var $left = $('<div class="attachment-card-left"></div>');
                $left.append('<div class="attachment-card-icon"><i class="fa ' + iconClass + '"></i></div>');

                var $info = $('<div class="attachment-card-info"></div>');
                $info.append('<div class="attachment-card-title" title="' + escapeMailHtml(att.name) + '">' + escapeMailHtml(att.label || att.name) + '</div>');
                $info.append('<div class="attachment-card-sub"><span>' + escapeMailHtml(att.sublabel || '') + '</span> <span class="attachment-badge ' + badgeClass + '">' + badgeText + '</span></div>');

                $left.append($info);
                $item.append($left);

                var $removeBtn = $('<button type="button" class="btn-remove-attachment" title="Remove this attachment"><i class="fa fa-trash-o"></i></button>');
                $removeBtn.data('att-idx', idx);
                $removeBtn.click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var index = $(this).data('att-idx');
                    activeAttachments.splice(index, 1);
                    renderReminderAttachments();
                });

                $item.append($removeBtn);
                $c.append($item);
            });

            updateMailPreview();
        }

        // Trigger manual file selection from phone or PC
        $('#btnTriggerAddAttachment').click(function (e) {
            e.preventDefault();
            $('#reminderManualFileInput').val('').click();
        });

        // Add manual files on selection
        $('#reminderManualFileInput').on('change', function () {
            var files = this.files;
            if (!files || files.length === 0) return;

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var fSizeStr = '';
                if (file.size >= 1048576) {
                    fSizeStr = (file.size / 1048576).toFixed(2) + ' MB';
                } else if (file.size >= 1024) {
                    fSizeStr = (file.size / 1024).toFixed(1) + ' KB';
                } else {
                    fSizeStr = file.size + ' B';
                }

                activeAttachments.push({
                    id: 'manual_' + Date.now() + '_' + i,
                    type: 'manual',
                    file: file,
                    name: file.name,
                    label: file.name,
                    sublabel: fSizeStr,
                    badge: 'Manual Upload'
                });
            }

            renderReminderAttachments();
        });

        // Switch to Preview Tab
        $('#tabBtnPreview').click(function () {
            $(this).addClass('active');
            $('#tabBtnEdit').removeClass('active');
            updateMailPreview();
            $('#modalReminderBodyEditor').hide();
            $('#modalReminderBodyPreview').show();
        });

        // Switch to Edit Body Tab
        $('#tabBtnEdit').click(function () {
            $(this).addClass('active');
            $('#tabBtnPreview').removeClass('active');
            $('#modalReminderBodyPreview').hide();
            $('#modalReminderBodyEditor').show();
            $('#modalReminderBodyIntro').focus();
        });

        // Detect edits in real-time
        $('#modalReminderBodyIntro, #modalReminderBodyClosing').on('input', function () {
            var curIntro = $('#modalReminderBodyIntro').val();
            var curClosing = $('#modalReminderBodyClosing').val();
            if (curIntro !== defaultGeneratedIntro || curClosing !== defaultGeneratedClosing) {
                $('#modalReminderIsCustom').val('1');
                $('#btnResetMailBody').show();
            } else {
                $('#modalReminderIsCustom').val('0');
                $('#btnResetMailBody').hide();
            }
        });

        // Reset to default auto-generated body
        $('#btnResetMailBody').click(function () {
            $('#modalReminderBodyIntro').val(defaultGeneratedIntro);
            $('#modalReminderBodyClosing').val(defaultGeneratedClosing);
            $('#modalReminderSubject').val(defaultGeneratedSubject);
            $('#modalReminderIsCustom').val('0');
            $(this).hide();
            updateMailPreview();
        });

        // Open Send Due Reminder Modal
        $('#tab_customer_list').on('click', '.btnSendReminderMail', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var id = $btn.data('id');
            var name = $btn.data('name') || '';
            var contactPerson = $btn.data('contact-person') || '';
            var phone = $btn.data('phone') || '-';
            var email = $btn.data('email') || '';
            var service = $btn.data('service') || 'Certification Services';
            var balanceNum = parseFloat($btn.data('balance') || '0');
            var totalInvNum = parseFloat($btn.data('total-inv') || '0');
            var totalPaidNum = parseFloat($btn.data('total-paid') || '0');
            var totalTdsNum = parseFloat($btn.data('total-tds') || '0');

            var fmtBalance = balanceNum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var fmtInv = totalInvNum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var fmtPaid = totalPaidNum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var fmtTds = totalTdsNum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            activeReminderData = {
                id: id,
                headerKey: $btn.attr('data-header-key'),
                name: name,
                contactPerson: contactPerson,
                phone: phone,
                email: email,
                service: service,
                balance: balanceNum,
                totalInv: totalInvNum,
                totalPaid: totalPaidNum,
                totalTds: totalTdsNum,
                fmtBalance: fmtBalance,
                fmtInv: fmtInv,
                fmtPaid: fmtPaid,
                fmtTds: fmtTds
            };

            $('#modalReminderCustomerId').val(id);
            $('#modalReminderClientName').text(name);
            $('#modalReminderContactPerson').text(contactPerson || 'Accounts');
            $('#modalReminderPhone').text(phone);
            $('#modalReminderDueAmount').text(fmtBalance);
            $('#modalReminderEmail').val(email);

            // Generate default subject and editable parts (excluding the locked bank details)
            defaultGeneratedSubject = "Outstanding Payment Reminder - ₹" + fmtBalance + " - " + name;
            $('#modalReminderSubject').val(defaultGeneratedSubject);

            var salutation = contactPerson ? contactPerson : name;
            defaultGeneratedIntro = "Dear " + salutation + " (" + name + "),\n\n"
                + "Greetings from AITF MANAGEMENT CERTIFICATION LIMITED.\n\n"
                + "We hope this email finds you well. This is a gentle reminder regarding the outstanding payment balance pending in your account.\n\n"
                + "--- Account & Payment Statement ---\n"
                + "Client Name: " + name + "\n"
                + "Service Name: " + service + "\n"
                + "Total Invoiced Amount: ₹" + fmtInv + "\n"
                + "Total Payment Received: ₹" + fmtPaid + "\n"
                + (totalTdsNum > 0 ? "TDS Deducted: ₹" + fmtTds + "\n" : "")
                + "Total Outstanding Due: ₹" + fmtBalance + "/-\n\n"
                + "As per our accounting records, the total outstanding balance of ₹" + fmtBalance + "/- is currently overdue. We kindly request you to clear the outstanding balance at your earliest convenience.\n\n"
                + "You may transfer the amount via NEFT / RTGS / IMPS to our company account details below:";

            defaultGeneratedClosing = "If the payment has already been initiated or processed, please share the transaction confirmation / UTR details with us so that our accounts team can reconcile your ledger.\n\n"
                + "Should you have any queries or require copies of the invoices, please feel free to contact us.\n\n"
                + "Thank you for your prompt attention and cooperation.\n\n"
                + "Best Regards,\n"
                + "Accounts & Finance Team\n"
                + "AITF MANAGEMENT CERTIFICATION LIMITED\n"
                + "Phone: " + firmPhone + "\n"
                + "Email: aitfacct@gmail.com\n"
                + "Website: www.aitf.co.in";

            $('#modalReminderBodyIntro').val(defaultGeneratedIntro);
            $('#modalReminderBodyClosing').val(defaultGeneratedClosing);
            $('#modalReminderIsCustom').val('0');

            // Initialize default attachments from button data (Invoices)
            activeAttachments = [];
            var rawInvoices = $btn.attr('data-invoices');
            if (rawInvoices) {
                try {
                    var invList = JSON.parse(rawInvoices);
                    if (Array.isArray(invList)) {
                        invList.forEach(function (inv) {
                            var invNum = inv.number || ('Invoice #' + inv.id);
                            var cleanNum = invNum.replace(/[^a-zA-Z0-9_\-\.]/g, '_');
                            var amtFmt = parseFloat(inv.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            activeAttachments.push({
                                id: 'inv_' + inv.id,
                                type: 'invoice',
                                invoice_id: parseInt(inv.id, 10),
                                name: 'Invoice-' + cleanNum + '.pdf',
                                label: invNum + ' (Invoice PDF)',
                                sublabel: 'Amount: ₹' + amtFmt,
                                badge: 'Service Invoice'
                            });
                        });
                    }
                } catch (e) {}
            }
            renderReminderAttachments();

            // Fetch / verify service invoice attachments from server
            $.getJSON('getPaymentDueAttachments.php', { customerId: id, headerKey: activeReminderData.headerKey })
                .done(function (res) {
                    if (res && res.success && Array.isArray(res.attachments)) {
                        var changed = false;
                        res.attachments.forEach(function (serverAtt) {
                            var exists = false;
                            for (var j = 0; j < activeAttachments.length; j++) {
                                if (activeAttachments[j].id === serverAtt.id) {
                                    exists = true;
                                    break;
                                }
                            }
                            if (!exists) {
                                activeAttachments.push(serverAtt);
                                changed = true;
                            }
                        });
                        if (changed) {
                            renderReminderAttachments();
                        }
                    }
                });

            // Reset tabs to Preview mode
            $('#tabBtnPreview').addClass('active');
            $('#tabBtnEdit').removeClass('active');
            $('#btnResetMailBody').hide();
            $('#modalReminderBodyPreview').show();
            $('#modalReminderBodyEditor').hide();
            updateMailPreview();

            $('#modalReminderStatusMsg').removeClass('status-info status-success status-error').hide().empty();
            $('#btnConfirmSendDueReminder').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');

            $('#duePaymentReminderModal').modal('show');
        });

        // Submit Send Due Reminder Email
        $('#btnConfirmSendDueReminder').click(function () {
            var customerId = $('#modalReminderCustomerId').val();
            var recipientEmail = $.trim($('#modalReminderEmail').val());
            var subject = $.trim($('#modalReminderSubject').val());
            var introVal = $.trim($('#modalReminderBodyIntro').val());
            var closingVal = $.trim($('#modalReminderBodyClosing').val());
            var isCustom = $('#modalReminderIsCustom').val();
            var $msg = $('#modalReminderStatusMsg');
            var $btn = $(this);

            if (!recipientEmail) {
                $msg.removeClass('status-info status-success').addClass('status-error')
                    .html('<i class="fa fa-exclamation-circle"></i> Please enter a recipient email address.').show();
                $('#modalReminderEmail').focus();
                return;
            }

            if (!subject) {
                $msg.removeClass('status-info status-success').addClass('status-error')
                    .html('<i class="fa fa-exclamation-circle"></i> Please enter an email subject.').show();
                $('#modalReminderSubject').focus();
                return;
            }

            if (!introVal && !closingVal) {
                $msg.removeClass('status-info status-success').addClass('status-error')
                    .html('<i class="fa fa-exclamation-circle"></i> Email body cannot be empty.').show();
                $('#modalReminderBodyIntro').focus();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.removeClass('status-success status-error').addClass('status-info')
                .html('<i class="fa fa-spinner fa-spin"></i> Sending payment reminder email, please wait...').show();

            var formData = new FormData();
            formData.append('customerId', customerId);
            formData.append('headerKey', activeReminderData.headerKey);
            formData.append('recipientEmail', recipientEmail);
            formData.append('subject', subject);
            formData.append('body_intro', introVal);
            formData.append('body_closing', closingVal);
            formData.append('is_custom', isCustom);

            // Separate kept invoices, service docs, and manual files
            var keptInvoices = [];
            var keptServiceDocs = [];
            activeAttachments.forEach(function (att) {
                if (att.type === 'invoice') {
                    keptInvoices.push(att.invoice_id);
                } else if (att.type === 'service_doc') {
                    keptServiceDocs.push(att.doc_path);
                } else if (att.type === 'manual' && att.file) {
                    formData.append('manual_attachments[]', att.file, att.name);
                }
            });
            formData.append('service_invoices', JSON.stringify(keptInvoices));
            formData.append('service_files', JSON.stringify(keptServiceDocs));

            $.ajax({
                url: 'sendPaymentDueReminder.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        $msg.removeClass('status-info status-error').addClass('status-success')
                            .html('<i class="fa fa-check-circle"></i> ' + (res.message || 'Email sent successfully!')).show();
                        $btn.html('<i class="fa fa-check"></i> Sent');

                        // Update reminder email count under Mail button
                        var $countContainer = $('#tab_customer_list tr[data-customer-id="' + customerId + '"]').find('.reminder-count-container');
                        var $countSpan = $countContainer.find('.reminder-count-val');
                        var currentCount = parseInt($countSpan.first().text(), 10) || 0;
                        var newCount = currentCount + 1;
                        $countSpan.text(newCount);
                        $countContainer.attr('data-count', newCount);

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

        // View Reminder Email History (Date & Time logs)
        $('#tab_customer_list').on('click', '.btnViewReminderHistory', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var customerId = $(this).attr('data-customer-id');
            var customerName = $(this).attr('data-customer-name') || 'Client';
            var headerKey = $(this).attr('data-header-key') || '';
            var service = $(this).attr('data-service') || '';
            var count = parseInt($(this).attr('data-count'), 10) || 0;

            $('#historyModalClientName').text(customerName);
            var serviceDisplay = headerKey ? ('Service Name: ' + headerKey) : (service ? ('Service Name: ' + service) : '');
            $('#historyModalServiceInfo').text(serviceDisplay);
            $('#historyModalTotalCount').text(count);
            $('#historyModalTimelineLoading').show();
            $('#historyModalTimelineEmpty').hide();
            $('#historyModalTimelineList').hide().empty();

            $('#dueReminderHistoryModal').modal('show');

            $.getJSON('getReminderMailHistory.php', { customerId: customerId, headerKey: headerKey })
                .done(function (res) {
                    $('#historyModalTimelineLoading').hide();
                    if (res && res.success && Array.isArray(res.logs) && res.logs.length > 0) {
                        $('#historyModalTotalCount').text(res.logs.length);
                        var html = '';
                        res.logs.forEach(function (log, idx) {
                            var orderNum = res.logs.length - idx;
                            var dtDisplay = log.formatted_datetime || ((log.date || '') + ' ' + (log.time || ''));
                            var recipientDisplay = log.recipient || '-';
                            var ccDisplay = log.cc || 'aitfmcl@gmail.com, aitfacct@gmail.com';
                            var serviceBadge = log.service || log.header_key || '';

                            html += '<div class="timeline-card-item">';
                            html += '  <div class="timeline-card-header">';
                            html += '    <div class="timeline-date-badge"><i class="fa fa-calendar-check-o"></i> ' + escapeMailHtml(dtDisplay) + '</div>';
                            html += '    <span class="timeline-order-pill">Reminder #' + orderNum + '</span>';
                            html += '  </div>';
                            html += '  <div class="timeline-detail-row" style="margin-bottom: 6px;">';
                            html += '    <span class="timeline-detail-item" style="font-weight: 700; color: #0d4d8b;"><i class="fa fa-user-circle" style="color: #2563eb; font-size: 13px;"></i> Sent by: <span class="timeline-user-val" style="background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 6px; border: 1px solid #bfdbfe; font-weight: 800;">' + escapeMailHtml(log.sent_by || 'Staff') + '</span></span>';
                            if (serviceBadge) {
                                html += '    <span class="timeline-service-pill"><i class="fa fa-tag"></i> Service Name: ' + escapeMailHtml(serviceBadge) + '</span>';
                            }
                            html += '  </div>';
                            html += '  <div class="timeline-detail-row">';
                            html += '    <span class="timeline-detail-item"><i class="fa fa-envelope"></i> <strong>To:</strong> <span class="timeline-email-val">' + escapeMailHtml(recipientDisplay) + '</span></span>';
                            html += '    <span class="timeline-detail-item"><i class="fa fa-share"></i> <strong>CC:</strong> <span class="timeline-cc-val">' + escapeMailHtml(ccDisplay) + '</span></span>';
                            html += '  </div>';

                            if (log.attachments && log.attachments.length > 0) {
                                html += '  <div class="timeline-attachments-box">';
                                html += '    <span style="font-weight: 700; color: #475569; margin-right: 6px;"><i class="fa fa-paperclip"></i> ' + log.attachments.length + ' Attachment(s):</span>';
                                log.attachments.forEach(function (att) {
                                    var attName = typeof att === 'string' ? att : (att.name || att.label || 'File');
                                    html += '    <span class="timeline-att-badge"><i class="fa fa-file-pdf-o text-danger"></i> ' + escapeMailHtml(attName) + '</span>';
                                });
                                html += '  </div>';
                            } else if (log.attachments_count > 0) {
                                html += '  <div class="timeline-attachments-box">';
                                html += '    <span style="font-weight: 700; color: #475569;"><i class="fa fa-paperclip"></i> ' + log.attachments_count + ' Attachment(s) attached</span>';
                                html += '  </div>';
                            }
                            html += '</div>';
                        });
                        $('#historyModalTimelineList').html(html).show();
                        $('#historyModalTimelineEmpty').hide();
                    } else {
                        $('#historyModalTotalCount').text('0');
                        $('#historyModalTimelineList').hide().empty();
                        $('#historyModalTimelineEmpty').show();
                    }
                })
                .fail(function () {
                    $('#historyModalTimelineLoading').hide();
                    $('#historyModalTotalCount').text('0');
                    $('#historyModalTimelineList').hide().empty();
                    $('#historyModalTimelineEmpty').html('<i class="fa fa-exclamation-triangle text-danger" style="font-size: 28px; margin-bottom: 8px; display: block;"></i><h5 style="color: #b91c1c;">Failed to load history</h5><p style="font-size: 12px; color: #64748b;">An error occurred while fetching reminder history.</p>').show();
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
                "width": "24%",
                "targets": 0
            },
            {
                "width": "13%",
                "targets": 1
            },
            {
                "width": "12%",
                "targets": 2
            },
            {
                "width": "12%",
                "targets": 3
            },
            {
                "width": "12%",
                "targets": 4
            },
            {
                "width": "7%",
                "targets": 5,
                "orderable": false,
                "searchable": false
            },
            {
                "width": "20%",
                "targets": 6
            }
        ]
    });
    table.columns.adjust().draw();
</script>

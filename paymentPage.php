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

        /* Due Reminder Modal Dark Mode */
        html.theme-dark #duePaymentReminderModal .modal-content {
            background: var(--pp-surface) !important;
            border: 1px solid var(--pp-border) !important;
            color: var(--pp-text) !important;
        }
        html.theme-dark #duePaymentReminderModal .modal-body {
            background: var(--pp-bg) !important;
        }
        html.theme-dark #duePaymentReminderModal .modal-footer {
            background: var(--pp-surface) !important;
            border-top: 1px solid var(--pp-border) !important;
        }
        html.theme-dark #duePaymentReminderModal .form-control {
            background-color: var(--pp-surface-2) !important;
            border-color: var(--pp-border-2) !important;
            color: var(--pp-text) !important;
        }
        html.theme-dark #duePaymentReminderModal label,
        html.theme-dark #duePaymentReminderModal h5 {
            color: var(--pp-text) !important;
        }
        html.theme-dark #duePaymentReminderModal div[style*="background: #ffffff"] {
            background: var(--pp-surface) !important;
            border-color: var(--pp-border) !important;
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
                                        <small><a>Amount Pending</a></small>
                                    </th>
                                    <th class="">
                                        <small><a>Comments</a></small>
                                    </th>
                                    <th class="text-center" style="width: 70px;">
                                        <small><a>Action</a></small>
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
    <div class="modal-dialog modal-md" style="max-width: 580px; margin-top: 60px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 12px 40px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%); color: #ffffff; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
                <h4 class="modal-title" style="font-size: 16px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa fa-envelope-o" style="font-size: 18px;"></i> Send Outstanding Payment Reminder
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #ffffff; opacity: 0.9; font-size: 24px; line-height: 1; margin: 0; background: none; border: none;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px 24px; background: #f8fafc;">
                <!-- Client Summary Card -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px;">
                        <div>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Client Details</span>
                            <h5 id="modalReminderClientName" style="margin: 4px 0 2px 0; font-size: 15px; font-weight: 800; color: #0f172a;">Client Name</h5>
                            <div style="font-size: 12.5px; color: #475569;">
                                <span id="modalReminderContactPerson"><i class="fa fa-user" style="color: #0d4d8b; margin-right: 4px;"></i> </span> &bull; 
                                <span id="modalReminderPhone"><i class="fa fa-phone" style="color: #0d4d8b; margin-right: 4px;"></i> </span>
                            </div>
                        </div>
                        <span id="modalReminderService" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">CA</span>
                    </div>

                    <!-- Highlighted Outstanding Due Banner -->
                    <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1.5px solid #fca5a5; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; font-weight: 700; color: #991b1b; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa fa-clock-o" style="font-size: 16px; color: #dc2626;"></i> Total Outstanding Due:
                        </span>
                        <strong style="font-size: 18px; font-weight: 900; color: #dc2626;">
                            ₹<span id="modalReminderDueAmount">0.00</span>
                        </strong>
                    </div>
                </div>

                <!-- Email Form -->
                <form id="reminderMailForm" onsubmit="return false;">
                    <input type="hidden" id="modalReminderCustomerId" value="">
                    
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">
                            Recipient Email Address <span style="color: #dc2626;">*</span>
                        </label>
                        <div style="position: relative;">
                            <i class="fa fa-envelope" style="position: absolute; left: 12px; top: 12px; color: #94a3b8; font-size: 14px;"></i>
                            <input type="email" id="modalReminderEmail" class="form-control" placeholder="client@example.com" required
                                style="padding-left: 36px; height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 500;">
                        </div>
                        <small style="color: #64748b; font-size: 11px; display: block; margin-top: 4px;">
                            <i class="fa fa-info-circle"></i> Auto-fetched from client profile. You can edit or add additional emails (comma separated).
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">
                            Optional Custom Note / Remarks
                        </label>
                        <textarea id="modalReminderNote" class="form-control" rows="2" placeholder="e.g. Please clear payment before Friday to avoid account hold..."
                            style="border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px; resize: vertical;"></textarea>
                    </div>

                    <div id="modalReminderStatusMsg" style="font-size: 13px; font-weight: 600; min-height: 20px; margin-bottom: 10px; display: none;"></div>
                </form>
            </div>
            <div class="modal-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="font-weight: 600; border-radius: 6px; padding: 6px 16px;">Cancel</button>
                <button type="button" id="btnConfirmSendDueReminder" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #0d4d8b 0%, #083b6a 100%); border: none; font-weight: 700; border-radius: 6px; padding: 6px 20px; display: inline-flex; align-items: center; gap: 6px;">
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
            var service = $btn.data('service') || 'Certification';
            var balance = $btn.data('balance') || '0.00';

            $('#modalReminderCustomerId').val(id);
            $('#modalReminderClientName').text(name);
            $('#modalReminderContactPerson').html('<i class="fa fa-user" style="color: #0d4d8b; margin-right: 4px;"></i> ' + contactPerson);
            $('#modalReminderPhone').html('<i class="fa fa-phone" style="color: #0d4d8b; margin-right: 4px;"></i> ' + phone);
            $('#modalReminderService').text(service);
            $('#modalReminderDueAmount').text(parseFloat(balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#modalReminderEmail').val(email);
            $('#modalReminderNote').val('');
            $('#modalReminderStatusMsg').hide().empty();
            $('#btnConfirmSendDueReminder').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');

            $('#duePaymentReminderModal').modal('show');
        });

        // Submit Send Due Reminder Email
        $('#btnConfirmSendDueReminder').click(function () {
            var customerId = $('#modalReminderCustomerId').val();
            var recipientEmail = $.trim($('#modalReminderEmail').val());
            var customNote = $.trim($('#modalReminderNote').val());
            var $msg = $('#modalReminderStatusMsg');
            var $btn = $(this);

            if (!recipientEmail) {
                $msg.css('color', '#dc2626').text('Please enter a recipient email address.').show();
                $('#modalReminderEmail').focus();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            $msg.css('color', '#0d4d8b').text('Sending payment reminder email, please wait...').show();

            $.ajax({
                url: 'sendPaymentDueReminder.php',
                type: 'POST',
                data: {
                    customerId: customerId,
                    recipientEmail: recipientEmail,
                    customNote: customNote
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        $msg.css('color', '#16a34a').text(res.message || 'Email sent successfully!').show();
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
                        $msg.css('color', '#dc2626').text(res.message || 'Failed to send email.').show();
                        $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Email');
                    }
                },
                error: function (xhr, status, error) {
                    $msg.css('color', '#dc2626').text('Server error while sending email. Please try again.').show();
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

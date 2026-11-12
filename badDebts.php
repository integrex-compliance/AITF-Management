<div id="wrapper" class="active">
<?php
$pageIdentifier = "Bad Debts";
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_matrix.php';
require_once __DIR__ . '/include/bad_debts_helper.php';

date_default_timezone_set('Asia/Kolkata');

// Access Restriction: Accessible to everyone except Auditor
if (!aitf_can_view_bad_debts()) {
    echo "<script>alert('Unauthorized access. This page is not accessible to Auditor accounts.'); window.location.href='homePage.php';</script>";
    exit;
}

// Action Restriction: Only Top Management and Finance can approve or revert Bad Debts
$canManageBadDebts = aitf_can_manage_bad_debts();
$currentUsername = $_SESSION['username'] ?? 'User';

aitf_bad_debts_schema($db);
include_once './header.php';

// Fetch Bad Debts for both sections
$pendingDebts = aitf_get_bad_debts($db, ['approval_status' => 'pending']);
$approvedDebts = aitf_get_bad_debts($db, ['approval_status' => 'approved']);

$pendingCount = count($pendingDebts);
$approvedCount = count($approvedDebts);
?>

<style>
    #page-content-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f8fafc;
        color: #0f172a;
    }

    .bad-debts-wrapper {
        padding: 16px 22px 50px 22px;
    }

    .bad-debts-title-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .bad-debts-title-bar h2 {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bad-debts-title-bar h2 i {
        color: #dc2626;
        font-size: 19px;
    }

    .panel-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .panel-section-header {
        padding: 10px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .panel-section-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-section-search {
        position: relative;
        width: 290px;
        max-width: 100%;
    }

    .panel-section-search input {
        height: 32px;
        padding-left: 30px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        box-shadow: none;
    }

    .panel-section-search input:focus {
        border-color: #0d4d8b;
        outline: none;
        box-shadow: 0 0 0 2px rgba(13, 77, 139, 0.1);
    }

    .panel-section-search i {
        position: absolute;
        left: 10px;
        top: 9px;
        color: #94a3b8;
        font-size: 12px;
    }

    .bad-debts-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .bad-debts-table thead th {
        background: #f8fafc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        border-top: none;
        padding: 9px 12px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .bad-debts-table tbody td {
        padding: 9px 12px;
        font-size: 12px;
        color: #1e293b;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }

    .bad-debts-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .client-link {
        font-weight: 600;
        color: #0d4d8b;
        text-decoration: none;
    }

    .client-link:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .service-badge {
        display: inline-block;
        padding: 2px 7px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .invoice-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #0d4d8b;
        font-weight: 600;
        text-decoration: none;
    }

    .invoice-link:hover {
        text-decoration: underline;
    }

    .amount-display {
        font-weight: 700;
        color: #dc2626;
        font-size: 12.5px;
    }

    .note-content {
        max-width: 280px;
        font-size: 11.5px;
        color: #475569;
        line-height: 1.4;
        word-break: break-word;
    }

    .btn-action-accept {
        background: #16a34a;
        color: #ffffff !important;
        border: 1px solid #15803d;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-action-accept:hover {
        background: #15803d;
    }

    .btn-action-accept:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-action-revert {
        background: #d97706;
        color: #ffffff !important;
        border: 1px solid #b45309;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 9px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-action-revert:hover {
        background: #b45309;
    }

    .btn-action-revert:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .tag-readonly {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 3px;
        font-size: 10.5px;
        font-weight: 600;
    }

    .tag-readonly-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .tag-readonly-approved {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .empty-state-row td {
        text-align: center;
        padding: 26px 15px !important;
        color: #64748b;
        font-size: 12.5px;
    }
</style>

<div id="page-content-wrapper">
    <div class="bad-debts-wrapper">

        <!-- Simple Clean Page Header -->
        <div class="bad-debts-title-bar">
            <h2><i class="fa fa-ban"></i> Bad Debts</h2>
        </div>

        <!-- ======================================================= -->
        <!-- SECTION 1: BAD DEBTS MARKED BY STAFF                    -->
        <!-- ======================================================= -->
        <div class="panel-section" id="pendingSection">
            <div class="panel-section-header">
                <h3 class="panel-section-title">
                    <i class="fa fa-clock-o" style="color: #d97706;"></i>
                    <span>Bad Debts Marked by Staff</span>
                    <span class="badge" id="pendingBadge" style="background: #f59e0b; font-size: 11px; margin-left: 4px;">
                        <?php echo $pendingCount; ?>
                    </span>
                </h3>
                <div class="panel-section-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="pendingSearch" class="form-control" placeholder="Search marked bad debts..." autocomplete="off">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table bad-debts-table" id="pendingDebtsTable">
                    <thead>
                        <tr>
                            <th width="4%">#</th>
                            <th width="24%">Client Name &amp; ID</th>
                            <th width="12%">Service</th>
                            <th width="12%">Invoice</th>
                            <th width="12%">Amount</th>
                            <th width="16%">Note / Reason</th>
                            <th width="10%">Marked Date</th>
                            <th width="10%" style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pendingDebts)) {
                            $idx = 1;
                            foreach ($pendingDebts as $row) {
                                $invId = (int)($row['invoice_id'] ?? 0);
                                $invNo = trim((string)($row['invoice_no'] ?? ''));
                                if ($invNo === '' && !empty($row['final_invoice_no'])) {
                                    $invNo = trim(($row['final_prefix'] ?? '') . '-' . $row['final_invoice_no']);
                                }
                                $isProforma = ($row['invoice_type'] ?? '') === 'proforma';
                                $invPdfUrl = $isProforma 
                                    ? "invoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($row['customer_id'])
                                    : "finalInvoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($row['customer_id']);
                                ?>
                                <tr class="bad-debt-pending-row" 
                                    id="pending-row-<?php echo $row['id']; ?>"
                                    data-id="<?php echo $row['id']; ?>"
                                    data-amount="<?php echo floatval($row['amount']); ?>"
                                    data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                    data-client="<?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?>"
                                    data-cust-id="<?php echo htmlspecialchars($row['customer_id']); ?>"
                                    data-uid="<?php echo htmlspecialchars($row['unique_id'] ?? $row['customer_id']); ?>"
                                    data-contact="<?php echo htmlspecialchars($row['contact'] ?? ''); ?>"
                                    data-inv-id="<?php echo $invId; ?>"
                                    data-inv-no="<?php echo htmlspecialchars($invNo); ?>"
                                    data-inv-url="<?php echo htmlspecialchars($invPdfUrl); ?>"
                                    data-note="<?php echo htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-created-by="<?php echo htmlspecialchars($row['created_by'] ?? 'Staff'); ?>"
                                    data-created-date="<?php echo !empty($row['created_at']) ? date('d.m.Y', strtotime($row['created_at'])) : '-'; ?>">
                                    
                                    <td class="row-num"><?php echo $idx++; ?></td>
                                    <td>
                                        <div>
                                            <a href="customerAccount.php?customerid=<?php echo urlencode($row['customer_id']); ?>" class="client-link" target="_blank">
                                                <?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 1px;">
                                            <span>UID: <strong><?php echo htmlspecialchars($row['unique_id'] ?? $row['customer_id']); ?></strong></span>
                                            <?php if (!empty($row['contact'])) { ?>
                                                <span style="margin-left: 6px;">&bull; <i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['contact']); ?></span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-badge"><?php echo htmlspecialchars($row['service_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($invId > 0 || $invNo !== '') { ?>
                                            <a href="<?php echo htmlspecialchars($invPdfUrl); ?>" target="_blank" class="invoice-link">
                                                <i class="fa fa-file-pdf-o" style="color: #dc2626;"></i>
                                                <span><?php echo htmlspecialchars($invNo ?: ('#' . $invId)); ?></span>
                                            </a>
                                        <?php } else { ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="amount-display">₹<?php echo number_format(floatval($row['amount']), 2); ?></span>
                                    </td>
                                    <td>
                                        <div class="note-content" title="<?php echo htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($row['note'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo !empty($row['created_at']) ? date('d.m.Y', strtotime($row['created_at'])) : '-'; ?></div>
                                        <?php if (!empty($row['created_by'])) { ?>
                                            <div style="font-size: 10.5px; color: #64748b;">by <?php echo htmlspecialchars($row['created_by']); ?></div>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($canManageBadDebts) { ?>
                                            <div style="display: inline-flex; gap: 4px;">
                                                <button type="button" 
                                                    class="btn-action-accept approveBadDebtBtn" 
                                                    data-id="<?php echo $row['id']; ?>" 
                                                    data-amount="<?php echo number_format(floatval($row['amount']), 2); ?>"
                                                    data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                                    data-client="<?php echo htmlspecialchars($row['customer_name'] ?? ''); ?>"
                                                    title="Accept this Bad Debt and move to Accepted Bad Debts">
                                                    <i class="fa fa-check"></i> Accept
                                                </button>
                                                <button type="button" 
                                                    class="btn-action-revert revertBadDebtBtn" 
                                                    data-id="<?php echo $row['id']; ?>" 
                                                    data-amount="<?php echo number_format(floatval($row['amount']), 2); ?>"
                                                    data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                                    data-client="<?php echo htmlspecialchars($row['customer_name'] ?? ''); ?>"
                                                    title="Revert back to Due Payment">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            </div>
                                        <?php } else { ?>
                                            <span class="tag-readonly tag-readonly-pending">Pending Review</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr class="empty-state-row pending-empty-row">
                                <td colspan="8">No bad debts marked by staff currently pending.</td>
                            </tr>
                        <?php } ?>
                        <tr class="empty-state-row pending-no-match" style="display: none;">
                            <td colspan="8">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- SECTION 2: ACCEPTED BAD DEBTS (FINANCE PERSON APPROVAL) -->
        <!-- ======================================================= -->
        <div class="panel-section" id="approvedSection">
            <div class="panel-section-header">
                <h3 class="panel-section-title">
                    <i class="fa fa-check-circle" style="color: #16a34a;"></i>
                    <span>Accepted Bad Debts (Finance Person Approval)</span>
                    <span class="badge" id="approvedBadge" style="background: #10b981; font-size: 11px; margin-left: 4px;">
                        <?php echo $approvedCount; ?>
                    </span>
                </h3>
                <div class="panel-section-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="approvedSearch" class="form-control" placeholder="Search accepted bad debts..." autocomplete="off">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table bad-debts-table" id="approvedDebtsTable">
                    <thead>
                        <tr>
                            <th width="4%">#</th>
                            <th width="22%">Client Name &amp; ID</th>
                            <th width="11%">Service</th>
                            <th width="11%">Invoice</th>
                            <th width="11%">Amount</th>
                            <th width="15%">Note / Reason</th>
                            <th width="10%">Marked Date</th>
                            <th width="11%">Approved By</th>
                            <th width="5%" style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($approvedDebts)) {
                            $idx = 1;
                            foreach ($approvedDebts as $row) {
                                $invId = (int)($row['invoice_id'] ?? 0);
                                $invNo = trim((string)($row['invoice_no'] ?? ''));
                                if ($invNo === '' && !empty($row['final_invoice_no'])) {
                                    $invNo = trim(($row['final_prefix'] ?? '') . '-' . $row['final_invoice_no']);
                                }
                                $isProforma = ($row['invoice_type'] ?? '') === 'proforma';
                                $invPdfUrl = $isProforma 
                                    ? "invoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($row['customer_id'])
                                    : "finalInvoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($row['customer_id']);
                                ?>
                                <tr class="bad-debt-approved-row" 
                                    id="approved-row-<?php echo $row['id']; ?>"
                                    data-id="<?php echo $row['id']; ?>"
                                    data-amount="<?php echo floatval($row['amount']); ?>"
                                    data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                    data-client="<?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?>">
                                    
                                    <td class="row-num"><?php echo $idx++; ?></td>
                                    <td>
                                        <div>
                                            <a href="customerAccount.php?customerid=<?php echo urlencode($row['customer_id']); ?>" class="client-link" target="_blank">
                                                <?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 1px;">
                                            <span>UID: <strong><?php echo htmlspecialchars($row['unique_id'] ?? $row['customer_id']); ?></strong></span>
                                            <?php if (!empty($row['contact'])) { ?>
                                                <span style="margin-left: 6px;">&bull; <i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['contact']); ?></span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="service-badge"><?php echo htmlspecialchars($row['service_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($invId > 0 || $invNo !== '') { ?>
                                            <a href="<?php echo htmlspecialchars($invPdfUrl); ?>" target="_blank" class="invoice-link">
                                                <i class="fa fa-file-pdf-o" style="color: #dc2626;"></i>
                                                <span><?php echo htmlspecialchars($invNo ?: ('#' . $invId)); ?></span>
                                            </a>
                                        <?php } else { ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="amount-display">₹<?php echo number_format(floatval($row['amount']), 2); ?></span>
                                    </td>
                                    <td>
                                        <div class="note-content" title="<?php echo htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($row['note'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo !empty($row['created_at']) ? date('d.m.Y', strtotime($row['created_at'])) : '-'; ?></div>
                                        <?php if (!empty($row['created_by'])) { ?>
                                            <div style="font-size: 10.5px; color: #64748b;">by <?php echo htmlspecialchars($row['created_by']); ?></div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #166534;">
                                            <i class="fa fa-check"></i> <?php echo htmlspecialchars($row['approved_by'] ?? 'Finance'); ?>
                                        </div>
                                        <?php if (!empty($row['approved_at'])) { ?>
                                            <div style="font-size: 10.5px; color: #64748b;">
                                                <?php echo date('d.m.Y H:i', strtotime($row['approved_at'])); ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($canManageBadDebts) { ?>
                                            <button type="button" 
                                                class="btn-action-revert revertBadDebtBtn" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-amount="<?php echo number_format(floatval($row['amount']), 2); ?>"
                                                data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                                data-client="<?php echo htmlspecialchars($row['customer_name'] ?? ''); ?>"
                                                title="Revert back to Due Payment">
                                                <i class="fa fa-undo"></i>
                                            </button>
                                        <?php } else { ?>
                                            <span class="tag-readonly tag-readonly-approved">Approved</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr class="empty-state-row approved-empty-row">
                                <td colspan="9">No accepted bad debts recorded yet.</td>
                            </tr>
                        <?php } ?>
                        <tr class="empty-state-row approved-no-match" style="display: none;">
                            <td colspan="9">No matching records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
$(function () {
    var canManageBadDebts = <?php echo $canManageBadDebts ? 'true' : 'false'; ?>;
    var currentUserName = <?php echo json_encode($currentUsername); ?>;

    // Toast Notification
    function showToast(message, type) {
        type = type || 'success';
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success' : 'Notice',
                text: message,
                timer: 2400,
                showConfirmButton: false,
                position: 'center'
            });
        } else {
            alert(message);
        }
    }

    // Update section counts & numbers
    function refreshCounts() {
        var pRows = $('#pendingDebtsTable tbody tr.bad-debt-pending-row');
        $('#pendingBadge').text(pRows.length);
        pRows.each(function (i) {
            $(this).find('td.row-num').text(i + 1);
        });
        if (pRows.length === 0) {
            if ($('#pendingDebtsTable tbody tr.pending-empty-row').length === 0) {
                $('#pendingDebtsTable tbody').prepend('<tr class="empty-state-row pending-empty-row"><td colspan="8">No bad debts marked by staff currently pending.</td></tr>');
            }
        }

        var aRows = $('#approvedDebtsTable tbody tr.bad-debt-approved-row');
        $('#approvedBadge').text(aRows.length);
        aRows.each(function (i) {
            $(this).find('td.row-num').text(i + 1);
        });
        if (aRows.length === 0) {
            if ($('#approvedDebtsTable tbody tr.approved-empty-row').length === 0) {
                $('#approvedDebtsTable tbody').prepend('<tr class="empty-state-row approved-empty-row"><td colspan="9">No accepted bad debts recorded yet.</td></tr>');
            }
        }
    }

    // 1. Separate Search for Pending Debts
    $('#pendingSearch').on('keyup input', function () {
        var q = $(this).val().toLowerCase().trim();
        var matches = 0;
        var total = $('#pendingDebtsTable tbody tr.bad-debt-pending-row').length;

        $('#pendingDebtsTable tbody tr.bad-debt-pending-row').each(function () {
            var text = $(this).text().toLowerCase();
            var isMatch = (q === '' || text.indexOf(q) > -1);
            $(this).toggle(isMatch);
            if (isMatch) matches++;
        });

        if (total > 0) {
            $('.pending-no-match').toggle(matches === 0 && q !== '');
        }
    });

    // 2. Separate Search for Accepted Debts
    $('#approvedSearch').on('keyup input', function () {
        var q = $(this).val().toLowerCase().trim();
        var matches = 0;
        var total = $('#approvedDebtsTable tbody tr.bad-debt-approved-row').length;

        $('#approvedDebtsTable tbody tr.bad-debt-approved-row').each(function () {
            var text = $(this).text().toLowerCase();
            var isMatch = (q === '' || text.indexOf(q) > -1);
            $(this).toggle(isMatch);
            if (isMatch) matches++;
        });

        if (total > 0) {
            $('.approved-no-match').toggle(matches === 0 && q !== '');
        }
    });

    // Accept / Approve Bad Debt Action
    $(document).on('click', '.approveBadDebtBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var bdId = $btn.attr('data-id');
        var amount = $btn.attr('data-amount');
        var service = $btn.attr('data-service');
        var client = $btn.attr('data-client');
        var $row = $btn.closest('tr');

        var doApprove = function () {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'addPayment.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'approveBadDebt',
                    bad_debt_id: bdId
                }
            }).done(function (res) {
                if (!res || !res.success) {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Accept');
                    showToast((res && res.message) ? res.message : 'Could not approve Bad Debt.', 'error');
                    return;
                }

                showToast('Bad Debt accepted and approved successfully.', 'success');

                // Read data from pending row
                var rawAmount = $row.attr('data-amount');
                var clientName = $row.attr('data-client');
                var custId = $row.attr('data-cust-id');
                var uid = $row.attr('data-uid');
                var contact = $row.attr('data-contact');
                var invId = $row.attr('data-inv-id');
                var invNo = $row.attr('data-inv-no');
                var invUrl = $row.attr('data-inv-url');
                var note = $row.attr('data-note') || '';
                var createdBy = $row.attr('data-created-by') || 'Staff';
                var createdDate = $row.attr('data-created-date') || '-';
                var approverName = res.approved_by || currentUserName || 'Finance';
                var approvedTime = res.approved_at || '';

                var invoiceHtml = '<span style="color: #94a3b8;">-</span>';
                if (invId > 0 || invNo !== '') {
                    invoiceHtml = '<a href="' + invUrl + '" target="_blank" class="invoice-link">' +
                                  '<i class="fa fa-file-pdf-o" style="color: #dc2626;"></i> ' +
                                  '<span>' + (invNo || ('#' + invId)) + '</span></a>';
                }

                var contactHtml = contact ? ('<span style="margin-left: 6px;">&bull; <i class="fa fa-phone"></i> ' + contact + '</span>') : '';

                var actionHtml = '';
                if (canManageBadDebts) {
                    actionHtml = '<button type="button" class="btn-action-revert revertBadDebtBtn" ' +
                                 'data-id="' + bdId + '" data-amount="' + amount + '" ' +
                                 'data-service="' + service + '" data-client="' + clientName + '" ' +
                                 'title="Revert back to Due Payment"><i class="fa fa-undo"></i></button>';
                } else {
                    actionHtml = '<span class="tag-readonly tag-readonly-approved">Approved</span>';
                }

                var newRow = 
                    '<tr class="bad-debt-approved-row" id="approved-row-' + bdId + '" ' +
                    'data-id="' + bdId + '" data-amount="' + rawAmount + '" data-service="' + service + '" data-client="' + clientName + '">' +
                    '<td class="row-num">1</td>' +
                    '<td>' +
                        '<div><a href="customerAccount.php?customerid=' + encodeURIComponent(custId) + '" class="client-link" target="_blank">' + clientName + '</a></div>' +
                        '<div style="font-size: 11px; color: #64748b; margin-top: 1px;"><span>UID: <strong>' + uid + '</strong></span>' + contactHtml + '</div>' +
                    '</td>' +
                    '<td><span class="service-badge">' + service + '</span></td>' +
                    '<td>' + invoiceHtml + '</td>' +
                    '<td><span class="amount-display">₹' + amount + '</span></td>' +
                    '<td><div class="note-content" title="' + note.replace(/"/g, '&quot;') + '">' + note.replace(/\n/g, '<br>') + '</div></td>' +
                    '<td><div style="font-weight: 600;">' + createdDate + '</div><div style="font-size: 10.5px; color: #64748b;">by ' + createdBy + '</div></td>' +
                    '<td>' +
                        '<div style="font-weight: 600; color: #166534;"><i class="fa fa-check"></i> ' + approverName + '</div>' +
                        (approvedTime ? '<div style="font-size: 10.5px; color: #64748b;">' + approvedTime + '</div>' : '') +
                    '</td>' +
                    '<td style="text-align: center;">' + actionHtml + '</td>' +
                    '</tr>';

                $('#approvedDebtsTable tbody tr.approved-empty-row').remove();
                $('#approvedDebtsTable tbody').prepend(newRow);

                $row.fadeOut(300, function () {
                    $(this).remove();
                    refreshCounts();
                });
            }).fail(function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Accept');
                var msg = xhr.responseJSON && xhr.responseJSON.message || 'Server error. Please retry.';
                showToast(msg, 'error');
            });
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: 'Accept Bad Debt?',
                text: 'Approve bad debt of ₹' + amount + ' for ' + client + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Accept',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (res.isConfirmed) doApprove();
            });
        } else {
            if (confirm('Accept Bad Debt of ₹' + amount + ' for ' + client + '?')) {
                doApprove();
            }
        }
    });

    // Revert Bad Debt Action
    $(document).on('click', '.revertBadDebtBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var bdId = $btn.attr('data-id');
        var amount = $btn.attr('data-amount');
        var client = $btn.attr('data-client');
        var $row = $btn.closest('tr');

        var doRevert = function () {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'addPayment.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'revertBadDebt',
                    bad_debt_id: bdId
                }
            }).done(function (res) {
                if (!res || !res.success) {
                    $btn.prop('disabled', false).html('<i class="fa fa-undo"></i>');
                    showToast((res && res.message) ? res.message : 'Could not revert Bad Debt.', 'error');
                    return;
                }

                showToast('Bad Debt reverted back to payment.', 'success');
                $row.fadeOut(300, function () {
                    $(this).remove();
                    refreshCounts();
                });
            }).fail(function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-undo"></i>');
                var msg = xhr.responseJSON && xhr.responseJSON.message || 'Server error. Please retry.';
                showToast(msg, 'error');
            });
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: 'Revert to Payment?',
                text: 'Revert bad debt of ₹' + amount + ' for ' + client + ' back to pending payment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Revert',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (res.isConfirmed) doRevert();
            });
        } else {
            if (confirm('Revert bad debt of ₹' + amount + ' for ' + client + ' back to pending payment?')) {
                doRevert();
            }
        }
    });
});
</script>
</div>

<div id="wrapper" class="active">
<?php
$pageIdentifier = "Bad Debts";
include_once './dbConnect.php';
require_once __DIR__ . '/include/rbac_matrix.php';
require_once __DIR__ . '/include/bad_debts_helper.php';

date_default_timezone_set('Asia/Kolkata');

// Strict Access Restriction: Only Finance, Top Mgmt, and Admin
if (!aitf_can_access_bad_debts()) {
    echo "<script>alert('Unauthorized access. Only Finance, Admin, or Top Management can access Bad Debts.'); window.location.href='homePage.php';</script>";
    exit;
}

aitf_bad_debts_schema($db);
include_once './header.php';

$clientSearch = trim($_GET['clientSearch'] ?? '');
$serviceFilter = trim($_GET['serviceFilter'] ?? 'all');
$startDateStr = trim($_GET['startDate'] ?? '');
$endDateStr = trim($_GET['endDate'] ?? '');

$startDate = '';
$endDate = '';
if (!empty($startDateStr)) {
    $parts = explode('.', $startDateStr);
    if (count($parts) === 3) {
        $startDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}
if (!empty($endDateStr)) {
    $parts = explode('.', $endDateStr);
    if (count($parts) === 3) {
        $endDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }
}

$filters = [
    'clientSearch' => $clientSearch,
    'serviceFilter' => $serviceFilter,
    'startDate' => $startDate,
    'endDate' => $endDate
];

$badDebtsList = aitf_get_bad_debts($db, $filters);

$totalCount = count($badDebtsList);
$totalBadDebtAmount = 0.0;
foreach ($badDebtsList as $row) {
    $totalBadDebtAmount += floatval($row['amount'] ?? 0);
}

$availableServices = ['CA', 'SA-I', 'SA-II', 'RC', 'SP', 'Contract Review', 'PTR', 'Transition Audit', 'Generic Training', 'Other'];
?>

<style>
    #page-content-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f8fafc;
        color: #0f172a;
        -webkit-font-smoothing: antialiased;
    }

    .bad-debts-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 12px 20px 50px 20px !important;
        box-sizing: border-box !important;
    }

    /* Hero Header */
    .bd-hero-header {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 50%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 12px 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
    }

    .bd-hero-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
    }

    .bd-hero-title {
        font-size: 17px;
        font-weight: 800;
        color: #991b1b;
        margin: 0;
        letter-spacing: -0.01em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bd-hero-title i {
        color: #dc2626;
        font-size: 19px;
    }

    .bd-hero-stats {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .bd-stat-pill {
        background: #ffffff;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .bd-stat-pill .stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #64748b;
    }

    .bd-stat-pill .stat-value {
        font-size: 14px;
        font-weight: 800;
        color: #991b1b;
    }

    /* Filter Card */
    .bd-filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }

    .bd-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .bd-filter-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .bd-filter-field label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin: 0;
    }

    .bd-filter-input {
        height: 32px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        color: #1e293b;
        background: #ffffff;
        transition: all 0.2s;
    }

    .bd-filter-input:focus {
        border-color: #dc2626;
        outline: none;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.12);
    }

    /* Table styling */
    .bd-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    .bd-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .bd-table thead th {
        background: #f8fafc;
        color: #334155;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
        white-space: nowrap;
        vertical-align: middle;
    }

    .bd-table tbody td {
        padding: 10px 12px;
        font-size: 12.5px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        color: #1e293b;
    }

    .bd-table tbody tr:hover td {
        background-color: #fef2f2 !important;
    }

    .bd-client-link {
        font-weight: 700;
        color: #0d4d8b;
        text-decoration: none;
        transition: color 0.15s;
    }

    .bd-client-link:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .bd-badge-svc {
        display: inline-flex;
        align-items: center;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .bd-invoice-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s;
    }

    .bd-invoice-pill:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #dc2626;
        text-decoration: none;
    }

    .bd-amount-display {
        font-size: 13.5px;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -0.2px;
    }

    .bd-note-box {
        max-width: 260px;
        font-size: 12px;
        line-height: 1.4;
        color: #475569;
        background: #f8fafc;
        border-left: 3px solid #f87171;
        padding: 4px 8px;
        border-radius: 0 4px 4px 0;
        word-break: break-word;
    }

    .btn-revert-payment {
        background: linear-gradient(135deg, #d97706, #b45309);
        color: #ffffff !important;
        border: none;
        font-size: 11.5px;
        font-weight: 700;
        padding: 5px 11px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(217, 119, 6, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .btn-revert-payment:hover {
        background: linear-gradient(135deg, #b45309, #92400e);
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35);
        transform: translateY(-1px);
    }

    .btn-revert-payment:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .bd-empty-state {
        text-align: center;
        padding: 45px 20px;
        color: #64748b;
    }

    .bd-empty-state i {
        font-size: 38px;
        color: #94a3b8;
        margin-bottom: 12px;
    }

    /* Floating Fallback Toast (Centered) */
    #customBdToast {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999999;
        min-width: 320px;
        max-width: 440px;
        padding: 16px 24px;
        border-radius: 12px;
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.35);
        display: none;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }
    #customBdToast.toast-success { background: #16a34a; }
    #customBdToast.toast-error { background: #dc2626; }
    .bd-swal-center-container {
        z-index: 9999999 !important;
    }
    .bd-swal-center-popup {
        border-radius: 16px !important;
        padding: 24px 22px !important;
        max-width: 440px !important;
        box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.4) !important;
    }
</style>

<div id="page-content-wrapper">
    <div class="bad-debts-container">

        <!-- Compact Hero Header -->
        <div class="bd-hero-header">
            <h1 class="bd-hero-title">
                <i class="fa fa-ban"></i>
                <span>Bad Debts / Loss Payments</span>
            </h1>
            <div class="bd-hero-stats">
                <div class="bd-stat-pill">
                    <span class="stat-label">Total Bad Debts:</span>
                    <span class="stat-value" id="kpiTotalCount"><?php echo number_format($totalCount); ?></span>
                </div>
                <div class="bd-stat-pill">
                    <span class="stat-label">Total Loss Amount:</span>
                    <span class="stat-value" id="kpiTotalAmount">₹<?php echo number_format($totalBadDebtAmount, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bd-filter-card">
            <form method="GET" action="badDebts.php" class="bd-filter-form">
                <div class="bd-filter-field" style="flex: 2; min-width: 220px;">
                    <label>Search Client / Note / Invoice</label>
                    <input type="text" name="clientSearch" class="bd-filter-input" value="<?php echo htmlspecialchars($clientSearch); ?>" placeholder="Search client name, UID, invoice, or note..." />
                </div>
                <div class="bd-filter-field" style="flex: 1; min-width: 140px;">
                    <label>Service</label>
                    <select name="serviceFilter" class="bd-filter-input">
                        <option value="all">All Services</option>
                        <?php foreach ($availableServices as $svc) {
                            $sel = (strcasecmp($serviceFilter, $svc) === 0) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($svc) . '" ' . $sel . '>' . htmlspecialchars($svc) . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="bd-filter-field" style="min-width: 130px;">
                    <label>From Date</label>
                    <input type="text" name="startDate" class="bd-filter-input datepicker" value="<?php echo htmlspecialchars($startDateStr); ?>" placeholder="DD.MM.YYYY" autocomplete="off" />
                </div>
                <div class="bd-filter-field" style="min-width: 130px;">
                    <label>To Date</label>
                    <input type="text" name="endDate" class="bd-filter-input datepicker" value="<?php echo htmlspecialchars($endDateStr); ?>" placeholder="DD.MM.YYYY" autocomplete="off" />
                </div>
                <div class="bd-filter-field" style="flex-direction: row; gap: 8px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="height: 32px; padding: 4px 14px; font-weight: 700; font-size: 12px; background: #0d4d8b; border-color: #0d4d8b;">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <a href="badDebts.php" class="btn btn-sm btn-default" style="height: 32px; padding: 4px 12px; font-weight: 600; font-size: 12px;">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Bad Debts Table Card -->
        <div class="bd-table-card">
            <div class="table-responsive">
                <table class="table bd-table" id="badDebtsTable">
                    <thead>
                        <tr>
                            <th width="4%">#</th>
                            <th width="24%">Client Name &amp; ID</th>
                            <th width="12%">Against Service</th>
                            <th width="14%">Invoice</th>
                            <th width="12%">Bad Debt Amount</th>
                            <th width="18%">Note / Reason</th>
                            <th width="10%">Marked Date</th>
                            <th width="6%" style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($badDebtsList)) {
                            $rowNum = 1;
                            foreach ($badDebtsList as $bdRow) {
                                $invId = (int)($bdRow['invoice_id'] ?? 0);
                                $invNo = trim((string)($bdRow['invoice_no'] ?? ''));
                                if ($invNo === '' && !empty($bdRow['final_invoice_no'])) {
                                    $invNo = trim(($bdRow['final_prefix'] ?? '') . '-' . $bdRow['final_invoice_no']);
                                }
                                $isProforma = ($bdRow['invoice_type'] ?? '') === 'proforma';
                                $invPdfUrl = $isProforma 
                                    ? "invoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($bdRow['customer_id'])
                                    : "finalInvoicePDF.php?invoiceid=" . urlencode($invId) . "&invoiceCustomerId=" . urlencode($bdRow['customer_id']);
                                ?>
                                <tr class="bad-debt-row" data-id="<?php echo $bdRow['id']; ?>" data-amount="<?php echo floatval($bdRow['amount']); ?>">
                                    <td><?php echo $rowNum++; ?></td>
                                    <td>
                                        <div>
                                            <a href="customerAccount.php?customerid=<?php echo urlencode($bdRow['customer_id']); ?>" class="bd-client-link" target="_blank" title="Open Customer Account">
                                                <?php echo htmlspecialchars($bdRow['customer_name'] ?? 'Unknown'); ?>
                                            </a>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            <span>UID: <strong><?php echo htmlspecialchars($bdRow['unique_id'] ?? $bdRow['customer_id']); ?></strong></span>
                                            <?php if (!empty($bdRow['contact'])) { ?>
                                                <span style="margin-left: 6px;">&bull; <i class="fa fa-phone"></i> <?php echo htmlspecialchars($bdRow['contact']); ?></span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="bd-badge-svc" title="Service against which Bad Debt was recorded">
                                            <?php echo htmlspecialchars($bdRow['service_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($invId > 0 || $invNo !== '') { ?>
                                            <a href="<?php echo htmlspecialchars($invPdfUrl); ?>" target="_blank" class="bd-invoice-pill" title="Click to view Invoice PDF for this service">
                                                <i class="fa fa-file-pdf-o" style="color: #dc2626; font-size: 13px;"></i>
                                                <span><?php echo htmlspecialchars($invNo ?: ('Invoice #' . $invId)); ?></span>
                                            </a>
                                        <?php } else { ?>
                                            <span style="color: #94a3b8; font-size: 11.5px;">-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="bd-amount-display">
                                            ₹<?php echo number_format(floatval($bdRow['amount']), 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="bd-note-box" title="<?php echo htmlspecialchars($bdRow['note'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo nl2br(htmlspecialchars($bdRow['note'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 11.5px; color: #334155;">
                                            <?php echo !empty($bdRow['created_at']) ? date('d.m.Y', strtotime($bdRow['created_at'])) : '-'; ?>
                                        </div>
                                        <?php if (!empty($bdRow['created_by'])) { ?>
                                            <div style="font-size: 10.5px; color: #64748b;">
                                                by <?php echo htmlspecialchars($bdRow['created_by']); ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                            class="btn-revert-payment revertBadDebtBtn" 
                                            data-id="<?php echo $bdRow['id']; ?>" 
                                            data-amount="<?php echo number_format(floatval($bdRow['amount']), 2); ?>"
                                            data-service="<?php echo htmlspecialchars($bdRow['service_type']); ?>"
                                            data-client="<?php echo htmlspecialchars($bdRow['customer_name'] ?? ''); ?>"
                                            title="Revert this Bad Debt back to Pending Payment">
                                            <i class="fa fa-undo"></i> Revert to Payment
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr class="no-records-row">
                                <td colspan="8">
                                    <div class="bd-empty-state">
                                        <i class="fa fa-check-circle" style="color: #10b981; font-size: 34px;"></i>
                                        <div style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 4px;">No Active Bad Debts</div>
                                        <div style="font-size: 12px; color: #64748b;">There are currently no active bad debt loss payments recorded.</div>
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

<!-- Floating Toast Container -->
<div id="customBdToast">
    <i class="fa fa-check-circle toast-icon" style="font-size: 16px;"></i>
    <span class="toast-message"></span>
</div>

<script>
$(function () {
    // Toast notification helper (Centered)
    window.showBdToast = function (message, type) {
        type = type || 'success';
        if (typeof Swal !== 'undefined' && Swal.fire) {
            if (type === 'error' || type === 'warning') {
                Swal.fire({
                    icon: type,
                    title: type === 'error' ? 'Notice' : 'Information',
                    html: '<div style="font-size: 14.5px; font-weight: 600; color: #334155; line-height: 1.5; padding: 4px 0;">' + message + '</div>',
                    position: 'center',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: '<i class="fa fa-check" style="margin-right: 4px;"></i> OK',
                    timer: 3500,
                    timerProgressBar: true,
                    customClass: {
                        container: 'bd-swal-center-container',
                        popup: 'bd-swal-center-popup'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    html: '<div style="font-size: 14.5px; font-weight: 600; color: #334155; line-height: 1.5; padding: 4px 0;">' + message + '</div>',
                    position: 'center',
                    showConfirmButton: false,
                    timer: 2200,
                    timerProgressBar: true,
                    customClass: {
                        container: 'bd-swal-center-container',
                        popup: 'bd-swal-center-popup'
                    }
                });
            }
        } else {
            var $toast = $('#customBdToast');
            $toast.removeClass('toast-success toast-error').addClass('toast-' + type);
            $toast.find('.toast-message').text(message);
            $toast.find('.toast-icon').attr('class', type === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-triangle');
            $toast.fadeIn(200).delay(2600).fadeOut(250);
        }
    };

    // Revert to Payment Action
    $(document).on('click', '.revertBadDebtBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var bdId = $btn.attr('data-id');
        var amount = $btn.attr('data-amount');
        var service = $btn.attr('data-service');
        var client = $btn.attr('data-client');
        var $row = $btn.closest('tr');

        var doRevert = function () {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Reverting...');

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
                    $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Revert to Payment');
                    var msg = (res && res.message) ? res.message : 'Could not revert Bad Debt.';
                    showBdToast(msg, 'error');
                    return;
                }

                // Show success toaster
                showBdToast('Bad Debt of ₹' + amount + ' reverted to pending payment successfully!', 'success');

                // Fade out row and remove from table
                $row.css('background-color', '#fef3c7').fadeOut(400, function () {
                    $(this).remove();
                    updateTableStats();
                });
            }).fail(function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Revert to Payment');
                var msg = xhr.responseJSON && xhr.responseJSON.message || 'Server error. Please retry.';
                showBdToast(msg, 'error');
            });
        };

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: '<span style="font-size: 17px; font-weight: 700; color: #991b1b;"><i class="fa fa-undo" style="margin-right: 6px;"></i> Revert Bad Debt to Payment?</span>',
                html: '<div style="font-size: 13px; color: #334155; line-height: 1.5;">' +
                      'Are you sure you want to revert this Bad Debt entry?<br>' +
                      '<strong>Client:</strong> ' + client + '<br>' +
                      '<strong>Service:</strong> ' + service + ' &bull; <strong>Amount:</strong> ₹' + amount + '<br><br>' +
                      '<small style="color: #64748b;">The loss settlement will be removed and this amount will return to Due Payment.</small>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fa fa-undo"></i> Yes, Revert to Payment',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    doRevert();
                }
            });
        } else {
            if (confirm('Revert Bad Debt of ₹' + amount + ' for ' + client + ' back to pending payment?')) {
                doRevert();
            }
        }
    });

    // Update KPI stats dynamically after row removal
    function updateTableStats() {
        var $rows = $('#badDebtsTable tbody tr.bad-debt-row');
        var count = $rows.length;
        var total = 0;

        $rows.each(function (idx) {
            $(this).find('td:first').text(idx + 1);
            total += parseFloat($(this).attr('data-amount') || 0);
        });

        $('#kpiTotalCount').text(count);
        $('#kpiTotalAmount').text('₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        if (count === 0) {
            $('#badDebtsTable tbody').html(
                '<tr class="no-records-row"><td colspan="8">' +
                '<div class="bd-empty-state">' +
                '<i class="fa fa-check-circle" style="color: #10b981; font-size: 34px;"></i>' +
                '<div style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 4px;">No Active Bad Debts</div>' +
                '<div style="font-size: 12px; color: #64748b;">All bad debts have been resolved or reverted.</div>' +
                '</div></td></tr>'
            );
        }
    }
});
</script>
</div>

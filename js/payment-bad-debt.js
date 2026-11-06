/**
 * Bad Debt / Loss Payment Frontend Handler
 */
$(function () {
    // Toast / Alert notification helper with SweetAlert2 and fallback (Centered)
    window.showBdToast = function (message, type) {
        type = type || 'success';
        if (typeof Swal !== 'undefined' && Swal.fire) {
            if (type === 'error' || type === 'warning') {
                Swal.fire({
                    icon: type,
                    title: type === 'error' ? 'Validation Notice' : 'Notice',
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
            if (!$toast.length) {
                $('body').append(
                    '<div id="customBdToast" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999999; min-width: 320px; max-width: 440px; padding: 16px 24px; border-radius: 12px; color: #ffffff; font-size: 14px; font-weight: 600; text-align: center; box-shadow: 0 15px 40px rgba(0,0,0,0.35); display: none; align-items: center; justify-content: center; gap: 10px;">' +
                    '<i class="fa fa-check-circle toast-icon" style="font-size: 18px;"></i><span class="toast-message"></span>' +
                    '</div>'
                );
                $toast = $('#customBdToast');
            }
            $toast.css('background', type === 'success' ? '#16a34a' : (type === 'info' ? '#2563eb' : '#dc2626'));
            $toast.find('.toast-message').text(message);
            $toast.find('.toast-icon').attr('class', type === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-circle');
            $toast.fadeIn(200).delay(2600).fadeOut(250);
        }
    };

    // Clear error highlights on input
    $(document).on('input', '#modalBadDebtAmount, #modalBadDebtNote', function () {
        $(this).css({ 'border-color': '#cbd5e1', 'box-shadow': 'none' });
    });

    // Open Bad Debt Modal
    $(document).on('click', '.openBadDebtModalBtn', function (event) {
        event.preventDefault();
        if (typeof paymentMutationPending !== 'undefined' && (paymentMutationPending || paymentMutationQueued)) return;

        var $block = $(this).closest('.service-payment-block');
        var header = $block.attr('data-service');

        paymentMutation(function () {
            return paymentRequest({ action: 'getPaymentSettlement', service_type: header }).done(function (result) {
                if (!result || !result.success) {
                    showBdToast(result && result.message || 'Could not load header balance.', 'error');
                    return;
                }
                var remaining = Number(result.remaining);
                if (remaining <= 0) {
                    showBdToast('No remaining due balance for this service to mark as bad debt.', 'info');
                    return;
                }

                var $modal = $('#badDebtModal');
                $modal.data('settlement-header', header)
                      .data('settlement-remaining', result.remaining)
                      .removeData('header-creation');

                var blockNum = $block.index('.service-payment-block') + 1;
                var headerLabel = typeof paymentHeaderLabel === 'function' ? paymentHeaderLabel(header) : header;

                $('#badDebtModalLabelService').text(blockNum + '. ' + headerLabel);
                $('#badDebtModalServiceLabel').text(blockNum + '. ' + headerLabel);

                var invoicesHtml = '-';
                var firstInvId = '', firstInvNo = '';
                if (result.invoices && result.invoices.length > 0) {
                    var invStrings = [];
                    $.each(result.invoices, function (idx, inv) {
                        invStrings.push(inv.number + ' (₹' + inv.amount + ')');
                        if (idx === 0) {
                            firstInvId = inv.id;
                            firstInvNo = inv.number;
                        }
                    });
                    invoicesHtml = invStrings.join(', ');
                }
                $('#badDebtModalInvoicesList').text(invoicesHtml);
                $('#badDebtInvoiceId').val(firstInvId);
                $('#badDebtInvoiceNo').val(firstInvNo);

                $('#badDebtModalDueDisplay').text('₹' + remaining.toFixed(2));
                $('#modalBadDebtAmount').val(remaining.toFixed(2)).attr('max', remaining.toFixed(2)).css({ 'border-color': '#cbd5e1', 'box-shadow': 'none' });
                $('#modalBadDebtNote').val('').css({ 'border-color': '#cbd5e1', 'box-shadow': 'none' });
                $('#btnSaveBadDebt').prop('disabled', false).html('<i class="fa fa-check-circle" style="margin-right: 4px;"></i> Mark as Bad Debt');

                $modal.modal('show');
            }).fail(function () {
                showBdToast('Could not load header balance. Please retry.', 'error');
            });
        });
    });

    // Form submit prevention
    $(document).on('submit', '#badDebtForm', function (event) {
        event.preventDefault();
        $('#btnSaveBadDebt').trigger('click');
    });

    // Save Bad Debt
    $(document).on('click', '#btnSaveBadDebt', function () {
        if (typeof paymentMutationPending !== 'undefined' && (paymentMutationPending || paymentMutationQueued)) return;

        var $modal = $('#badDebtModal');
        var amount = Number($('#modalBadDebtAmount').val());
        var remaining = Number($modal.data('settlement-remaining'));
        var header = $modal.data('settlement-header');
        var note = $.trim($('#modalBadDebtNote').val());

        if (!header || !Number.isFinite(amount) || amount <= 0 || Math.round(amount * 100) > Math.round(Math.abs(remaining) * 100)) {
            showBdToast('Enter an amount greater than zero, up to the remaining due balance (₹' + Math.abs(remaining).toFixed(2) + ').', 'error');
            $('#modalBadDebtAmount').focus().css({ 'border-color': '#ef4444', 'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.2)' });
            return;
        }

        if (note === '') {
            showBdToast('Please enter a note / reason for marking as Bad Debt.', 'error');
            $('#modalBadDebtNote').focus().css({ 'border-color': '#ef4444', 'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.2)' });
            return;
        }

        var key = typeof paymentHeaderCreationKey === 'function' ? paymentHeaderCreationKey($modal, header) : ('bd_' + Date.now());
        var $button = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin" style="margin-right: 4px;"></i> Saving...');

        paymentMutation(function () {
            return paymentRequest({
                action: 'markBadDebt',
                service_type: header,
                bad_debt_amount: amount,
                expected_remaining: $modal.data('settlement-remaining'),
                settlement_key: key,
                note: note,
                invoice_id: $('#badDebtInvoiceId').val(),
                invoice_no: $('#badDebtInvoiceNo').val()
            }).done(function (result) {
                if (!result || !result.success) {
                    showBdToast(result && result.message || 'Bad Debt could not be saved.', 'error');
                    return;
                }
                paymentMutationPending = false;
                $modal.modal('hide');
                showBdToast('Loss payment of ₹' + amount.toFixed(2) + ' marked as Bad Debt successfully!', 'success');
                setTimeout(function () {
                    window.location.reload();
                }, 750);
            }).fail(function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message || 'Bad Debt could not be saved. Please retry.';
                showBdToast(msg, 'error');
            });
        }).always(function () {
            $button.prop('disabled', false).html('<i class="fa fa-check-circle" style="margin-right: 4px;"></i> Mark as Bad Debt');
        });
    });
});

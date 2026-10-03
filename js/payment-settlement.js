/* Settlements use the selected header's server-calculated balance. */
$(function () {
    $(document).on('click', '.openDiscountModalBtn', function (event) {
        event.preventDefault();
        if (paymentMutationPending || paymentMutationQueued) return;
        var $block = $(this).closest('.service-payment-block'), header = $block.attr('data-service');
        paymentMutation(function () {
            return paymentRequest({action:'getPaymentSettlement',service_type:header}).done(function (result) {
                if (!result || !result.success) { alert(result && result.message || 'Could not load header balance.'); return; }
                var $modal = $('#discountModal'), remaining = Number(result.remaining), excess = remaining < 0;
                $modal.data('settlement-header',header).data('settlement-remaining',result.remaining).removeData('header-creation');
                $('#discountModalLabel').text((excess ? 'Excess settle' : 'Short settle') + ' — ' + ($block.index('.service-payment-block')+1) + '. ' + paymentHeaderLabel(header));
                $('#modalDiscountAmount').val(Math.abs(remaining).toFixed(2)).attr('max',Math.abs(remaining).toFixed(2));
                $('#btnSaveDiscountSettlement').prop('disabled',remaining === 0);
                $('#discountModal').modal('show');
            }).fail(function () { alert('Could not load header balance. Please retry.'); });
        });
    });
    $(document).on('submit','#discountSettlementForm',function (event) { event.preventDefault(); $('#btnSaveDiscountSettlement').trigger('click'); });
    $(document).on('click','#btnSaveDiscountSettlement',function () {
        if (paymentMutationPending || paymentMutationQueued) return;
        var $modal = $('#discountModal'), amount = Number($('#modalDiscountAmount').val());
        var remaining = Number($modal.data('settlement-remaining')), header = $modal.data('settlement-header');
        if (!header || !Number.isFinite(amount) || amount <= 0 || Math.round(amount*100) > Math.round(Math.abs(remaining)*100)) {
            alert('Enter an amount greater than zero, up to this header balance.'); return;
        }
        var key = paymentHeaderCreationKey($modal,header), $button = $(this).prop('disabled',true);
        paymentMutation(function () {
            return paymentRequest({action:'saveDiscount',service_type:header,discount_amount:amount,expected_remaining:$modal.data('settlement-remaining'),
                settlement_key:key,discount_date:$('#modalDiscountDate').val()
            }).done(function (result) {
                if (!result || !result.success) { alert(result && result.message || 'Settlement could not be saved.'); return; }
                paymentMutationPending = false;
                window.location.reload();
            }).fail(function () { alert('Settlement could not be saved. Please retry.'); });
        }).always(function () { $button.prop('disabled',false); });
    });
});

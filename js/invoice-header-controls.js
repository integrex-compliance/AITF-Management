/* Invoice headers are independent of received payments. */
var invoiceHeaderServices = ['CA','SA-I','SA-II','RC','SP','Contract Review','PTR','Transition Audit','Generic Training','Other'];

function populateMoveInvoiceDropdown(currentService) {
    var $select = $('#finalInvoiceSectionService').empty(), seen = Object.create(null), index = 0;
    $('.service-payment-block').each(function () {
        var name = ($(this).attr('data-service') || '').trim(), key = name.toUpperCase();
        if (name && !seen[key]) {
            seen[key] = true;
            $('<option>').val(name).text((++index)+'. '+paymentHeaderLabel(name)).appendTo($select);
            if (currentService && key === currentService.toUpperCase()) currentService = name;
        }
    });
    if (currentService && !seen[currentService.toUpperCase()]) {
        $('<option>').val(currentService).text((++index)+'. '+paymentHeaderLabel(currentService)).appendTo($select);
    }
    if (currentService) $select.val(currentService);
    $select.data('original-service',currentService || $select.val() || '');
    $select.data('selected-service',$select.val() || '');
    $('<option>').val('__add_invoice_header__').attr('data-add-header','1').text('+ Add new header').appendTo($select);
}

function moveInvoiceToHeader(invoiceId, target, original) {
    if (paymentMutationPending || paymentMutationQueued) return;
    var $error = $('#moveInvoiceHeaderError').text('');
    if (!invoiceId || !target) { $error.text('Select an invoice and target header.'); return; }
    if (original && original.toUpperCase() === target.toUpperCase()) { $error.text('Invoice is already in this header.'); return; }
    var $controls = $('#btnConfirmMoveInvoice, #finalInvoiceSectionService').prop('disabled',true);
    return paymentMutation(function () {
        return $.ajax({url:'moveInvoiceHeader.php',type:'POST',dataType:'json',data:{invoice_id:invoiceId,customer_id:paymentCustomerId(),target_service:target}})
            .done(function (res) {
                if (!res || !res.success) { $error.text(res && res.message || 'Invoice could not be moved.'); return; }
                paymentMutationPending = false;
                window.location.reload();
            }).fail(function () { $error.text('Invoice could not be moved. Please retry.'); });
    }).always(function () { $controls.prop('disabled',false); });
}

function closeNewInvoiceHeader() {
    $('#newMoveInvoiceHeaderPanel').hide();
}

$(function () {
    $(document).on('click','#btnConfirmMoveInvoice',function () {
        moveInvoiceToHeader(Number($('#finalinvoiceModal .invoiceId').val()),$('#finalInvoiceSectionService').val(),$('#finalInvoiceSectionService').data('original-service'));
    });
    $(document).on('change','#finalInvoiceSectionService',function () {
        var $headers = $(this);
        $('#moveInvoiceHeaderError').text('');
        if (!$headers.find('option:selected').attr('data-add-header')) {
            $headers.data('selected-service',$headers.val());
            closeNewInvoiceHeader();
            return;
        }
        $headers.val($headers.data('selected-service') || $headers.data('original-service') || '');
        var $select = $('#newMoveInvoiceHeaderService').empty();
        invoiceHeaderServices.forEach(function (name) {
            $('<option>').val(name).text(name).appendTo($select);
        });
        $('#newMoveInvoiceHeaderName').val('');
        $select.trigger('change');
        $('#moveInvoiceHeaderError').text('');
        $('#newMoveInvoiceHeaderPanel').show();
        $select.focus();
    });
    $(document).on('change','#newMoveInvoiceHeaderService',function () {
        $('#newMoveInvoiceHeaderName').toggle($(this).val() === 'Other');
    });
    $(document).on('click','#btnCancelMoveInvoiceHeader',closeNewInvoiceHeader);
    $(document).on('hidden.bs.modal','#finalinvoiceModal',function () { closeNewInvoiceHeader(); $('#moveInvoiceHeaderError').text(''); });
    $(document).on('keydown','#newMoveInvoiceHeaderPanel input, #newMoveInvoiceHeaderPanel select',function (event) {
        if (event.key === 'Enter' || event.which === 13) { event.preventDefault(); $('#btnSaveMoveInvoiceHeader').trigger('click'); }
    });
    $(document).on('click','#btnSaveMoveInvoiceHeader',function () {
        if (paymentMutationPending || paymentMutationQueued) return;
        var service = $('#newMoveInvoiceHeaderService').val();
        if (service === 'Other') service = ($('#newMoveInvoiceHeaderName').val() || '').trim();
        var $error = $('#moveInvoiceHeaderError').text('');
        if (!service) { $error.text('Enter a service header name.'); return; }
        var original = $('#finalInvoiceSectionService').data('original-service');
        var $save = $(this), creationKey = paymentHeaderCreationKey($save,service);
        var $controls = $('#newMoveInvoiceHeaderPanel :input, #btnConfirmMoveInvoice, #finalInvoiceSectionService').prop('disabled',true);
        paymentMutation(function () {
            return paymentRequest({action:'createPaymentHeader',service_type:service,creation_key:creationKey}).done(function (res) {
                if (!res || !res.success) { $error.text(res && res.message || 'Header could not be created.'); return; }
                $save.removeData('header-creation');
                var name = res.service_type, found = false;
                $('.service-payment-block').each(function () {
                    var existing = $(this).attr('data-service') || '';
                    if (existing.toUpperCase() === name.toUpperCase()) { name = existing; found = true; }
                });
                if (!found) {
                    $('#service_blocks_container').append(renderServiceBlockTemplate(name,$('.service-payment-block').length,paymentCustomerId(),$('#finalinvoiceModal input[name="invoiceAddress"]').val() || '',invoiceHeaderServices,res.header_name || service));
                    reindexAllServiceBlocks();
                    $('#activeServicesCountBadge').text($('.service-payment-block').length+' Active');
                    recalculatePaymentSystem();
                }
                populateMoveInvoiceDropdown(original);
                $('#finalInvoiceSectionService').val(name).data('selected-service',name);
                closeNewInvoiceHeader();
            }).fail(function () { $error.text('Header could not be created. Please retry.'); });
        }).always(function () { $controls.prop('disabled',false); });
    });
});

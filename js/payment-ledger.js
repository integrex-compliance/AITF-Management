/* Account ledger: save only changed rows; serialize saves with destructive actions. */
var paymentAutoSaveTimer = null;
var isPaymentSaving = false;
var paymentSavePending = false;
var paymentMutationPending = false;
var paymentSavePromise = null;
var paymentMutationQueued = false;

function paymentHeaderLabel(key) {
    var $block = $('.service-payment-block').filter(function () { return $(this).attr('data-service') === key; }).first();
    return $block.attr('data-header-name') || key;
}
function paymentHeaderCreationKey($control, name) {
    var pending = $control.data('header-creation');
    if (!pending || pending.name !== name) {
        pending = {name:name, key:'header_' + Date.now() + '_' + Math.random().toString(36).slice(2)};
        $control.data('header-creation',pending);
    }
    return pending.key;
}
function applyPaymentHeaderRename($block, result) {
    var source = $block.attr('data-service'), key = result.service_type, label = result.header_name || key;
    $block.attr('data-service',key).attr('data-header-name',label);
    $block.find('[data-service]').filter(function () { return $(this).attr('data-service') === source; }).attr('data-service',key).data('service',key);
    var $select = $block.find('.serviceBlockTypeSelect');
    if (!$select.find('option').filter(function () { return $(this).val() === label; }).length) $('<option>').val(label).text(label).appendTo($select);
    $select.val(label);
    $block.find('.service-details-table tr.cb_subsection').each(function () {
        var $row = $(this), id = $row.find('.row-id').val();
        if (!Number(id)) $row.find('.serviceTypeName').val(key);
        if (result.payment_versions && result.payment_versions[id] !== undefined) $row.attr('data-version',result.payment_versions[id]);
        $row.data('saved-payment',paymentSnapshot(paymentRowData($row)));
    });
    var $move = $('#finalInvoiceSectionService');
    $move.find('option').filter(function () { return $(this).val() === source; }).val(key);
    ['original-service','selected-service'].forEach(function (attr) { if ($move.data(attr) === source) $move.data(attr,key); });
    reindexAllServiceBlocks();
    $move.find('option').not('[data-add-header]').each(function (index) { $(this).text((index+1)+'. '+paymentHeaderLabel($(this).val())); });
    recalculatePaymentSystem();
}
function paymentRows() { return $('.service-details-table tbody tr.cb_subsection'); }
function paymentCustomerId() { return $('#service_blocks_container').attr('data-customer'); }
function paymentRowData($row) {
    var key = $row.attr('data-client-key');
    if (!key) {
        key = 'row_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2);
        $row.attr('data-client-key', key);
    }
    return {
        id: Number($row.find('.row-id').val()) || 0,
        key: key,
        version: Number($row.attr('data-version')) || 0,
        header: $row.closest('.service-payment-block').attr('data-service') || 'CA',
        received: $row.find('.servicePaymentReceived').val() || '',
        tds: $row.find('.serviceTdsAmount').val() || '',
        discount: $row.attr('data-settlement-kind') && $row.find('.serviceDiscountDisplay').length ? String(($row.attr('data-settlement-kind') === 'excess' ? -1 : 1) * Number($row.find('.serviceDiscountDisplay').val() || 0)) : $row.find('.serviceDiscountAmount').val() || '',
        settlement_amount: $row.attr('data-settlement-kind') ? $row.find('.serviceDiscountDisplay').val() : undefined,
        date: ($row.find('.paymentDepositDate').val() || '').trim(),
        instrument: ($row.find('.serviceChequeDetails').val() || '').trim()
    };
}
function paymentSnapshot(row) {
    return JSON.stringify([row.header, row.received, row.tds, row.discount, row.date, row.instrument]);
}
function paymentTextMeaningful(value) {
    var text = String(value == null ? '' : value).trim();
    return text !== '' && text !== '-' && !(isFinite(Number(text)) && Number(text) === 0);
}
function paymentRowHasDetails(row) {
    return Number(row.received) !== 0 || Number(row.tds) !== 0 || Number(row.discount) !== 0 ||
        ['', '-', '0', '0000-00-00', '1970-01-01', '01.01.1970'].indexOf(row.date) === -1 || paymentTextMeaningful(row.instrument);
}
function paymentBlockHasDetails($block) {
    if ($block.find('.viewFinalInvoice[data-id]').filter(function () { return Number($(this).attr('data-id')) > 0; }).length) return true;
    var meaningful = false;
    $block.find('.invoiceNo, .invoiceDate, .invoiceAmount, .serviceBlockComment, .invoiceRemark').each(function () {
        if (paymentTextMeaningful($(this).val())) meaningful = true;
    });
    $block.find('.service-details-table tbody tr.cb_subsection').each(function () {
        var $row = $(this);
        if (paymentRowHasDetails(paymentRowData($row)) || $row.attr('data-preserve-details') === '1' || $row.hasClass('row-reconciled-locked') || /^(verified|yes|1)$/i.test($row.find('.serviceReconciliation').val() || '')) meaningful = true;
    });
    return meaningful;
}
function pruneEmptyPaymentBlocks() {
    if (isPaymentSaving || paymentMutationPending || paymentMutationQueued) return;
    var changed = false;
    $('.service-payment-block').each(function () {
        var $block = $(this);
        if (paymentBlockHasDetails($block)) {
            $block.removeAttr('data-draft-header');
            return;
        }
        // Let newly created headers be populated during this visit.
        if ($block.attr('data-draft-header') === '1') return;
        var dirty = false;
        $block.find('.service-details-table tbody tr.cb_subsection').each(function () {
            if (paymentRowNeedsSave($(this), paymentRowData($(this)))) dirty = true;
        });
        $block.find('.serviceBlockComment').each(function () {
            var saved = $(this).data('saved-note');
            if (saved === undefined) saved = this.defaultValue || '';
            if ($(this).data('note-promise') || $(this).val() !== saved) dirty = true;
        });
        if (dirty) return;
        var service = $block.attr('data-service');
        $block.prev('.service-block-divider').remove();
        $block.remove();
        $('#finalInvoiceSectionService option').filter(function () { return !$(this).attr('data-add-header') && $(this).val() === service; }).remove();
        changed = true;
    });
    if (changed) {
        $('.service-payment-block').first().prev('.service-block-divider').remove();
        if (typeof reindexAllServiceBlocks === 'function') reindexAllServiceBlocks();
        $('#finalInvoiceSectionService option').not('[data-add-header]').each(function (index) {
            $(this).text((index + 1)+'. '+paymentHeaderLabel($(this).val()));
        });
        $('#activeServicesCountBadge').text($('.service-payment-block').length+' Active');
        recalculatePaymentSystem();
    }
}
function paymentRowNeedsSave($row, row) {
    if ($row.hasClass('row-reconciled-locked')) return false;
    if (!row.id && !paymentRowHasDetails(row)) return false;
    return $row.data('saved-payment') !== paymentSnapshot(row);
}
function triggerPaymentAutoSave(immediate) {
    clearTimeout(paymentAutoSaveTimer);
    paymentAutoSaveTimer = null;
    if (immediate) return executePaymentAutoSave();
    paymentAutoSaveTimer = setTimeout(executePaymentAutoSave, 650);
}
function executePaymentAutoSave() {
    clearTimeout(paymentAutoSaveTimer);
    paymentAutoSaveTimer = null;
    if (paymentMutationPending) { paymentSavePending = true; return $.Deferred().resolve().promise(); }
    if (isPaymentSaving) { paymentSavePending = true; return paymentSavePromise; }
    var matched = [], rows = [];
    paymentRows().each(function () {
        var $row = $(this), row = paymentRowData($row);
        if (paymentRowNeedsSave($row, row)) { matched.push($row); rows.push(row); }
    });
    if (!rows.length) return $.Deferred().resolve().promise();
    var deferred = $.Deferred();
    paymentSavePromise = deferred.promise();
    isPaymentSaving = true;
    paymentSavePending = false;
    showAutoSaveIndicator('saving', 'Saving...');
    $.ajax({url: 'addPayment.php', type: 'POST', dataType: 'json', data: {
        action: 'autoSavePayments', customer: paymentCustomerId(), rows: JSON.stringify(rows)
    }}).done(function (res) {
        if (!res || !res.success || !Array.isArray(res.rows) || res.rows.length !== rows.length) {
            isPaymentSaving = false;
            paymentSavePending = false;
            showAutoSaveIndicator('error', res && res.message || 'Save failed');
            deferred.reject(res && res.message || 'Save failed');
            return;
        }
        res.rows.forEach(function (saved, i) {
            var $row = matched[i];
            $row.find('.row-id').val(saved.id || '');
            $row.attr('data-version', saved.version);
            $row.find('.toggleReconcileBtn').attr('data-pay-id', saved.id);
            $row.find('.infoDelete').attr('data-row-id', saved.id);
            $row.data('saved-payment', saved.replayed ? null : paymentSnapshot(rows[i]));
        });
        if (res.payid) $('#paymentAutoSaveStatus, .savePayment').attr('data-row-id', res.payid);
        isPaymentSaving = false;
        showAutoSaveIndicator('saved');
        // Include edits made while the request was in flight before resolving a flush.
        executePaymentAutoSave().done(function () { pruneEmptyPaymentBlocks(); deferred.resolve(); }).fail(function (error) { deferred.reject(error); });
    }).fail(function () {
        isPaymentSaving = false;
        paymentSavePending = false;
        showAutoSaveIndicator('error', 'Save failed. Your edits remain on this page.');
        deferred.reject('Save failed. Retry before leaving this page.');
    });
    return paymentSavePromise;
}

function paymentMutation(work) {
    if (paymentMutationPending || paymentMutationQueued) return $.Deferred().reject('A payment change is already in progress.').promise();
    var operation = $.Deferred();
    paymentMutationQueued = true;
    var $enabled = $('#service_blocks_container :input:enabled');
    $enabled.prop('disabled', true);
    function release() { paymentMutationQueued = false; $enabled.prop('disabled', false); pruneEmptyPaymentBlocks(); }
    $.when(executePaymentAutoSave(), flushPaymentNotes()).done(function () {
        paymentMutationPending = true;
        var pending;
        try { pending = work(); } catch (error) {
            paymentMutationPending = false; release(); operation.reject(error); throw error;
        }
        $.when(pending).done(function (value) { operation.resolve(value); }).fail(function (error) { operation.reject(error); }).always(function () {
            paymentMutationPending = false;
            release();
            if (paymentSavePending) triggerPaymentAutoSave(false);
        });
    }).fail(function (message) { release(); alert(message); operation.reject(message); });
    return operation.promise();
}
function autoSaveServiceBlockComment($input) {
    clearTimeout($input.data('save-timer'));
    if ($input.data('note-promise')) return $input.data('note-promise');
    var value = $input.val() || '';
    var saved = $input.data('saved-note');
    if (saved === undefined) saved = $input[0].defaultValue || '';
    if (value === saved) return $.Deferred().resolve().promise();
    var deferred = $.Deferred();
    $input.data('note-promise', deferred.promise()).css('border-color', '#f59e0b');
    paymentRequest({action:'saveServiceBlockComment', service_type:$input.attr('data-service'), comment:value})
        .done(function (res) {
            $input.removeData('note-promise');
            if (!res || !res.success) {
                $input.css('border-color','#ef4444'); showAutoSaveIndicator('error',res && res.message || 'Notes could not be saved.'); deferred.reject(res && res.message || 'Notes could not be saved.'); return;
            }
            $input.data('saved-note',value).css('border-color','#cbd5e1');
            autoSaveServiceBlockComment($input).done(function () { pruneEmptyPaymentBlocks(); deferred.resolve(); }).fail(function (error) { deferred.reject(error); });
        }).fail(function () {
            $input.removeData('note-promise').css('border-color','#ef4444');
            showAutoSaveIndicator('error','Notes could not be saved.'); deferred.reject('Notes could not be saved.');
        });
    return deferred.promise();
}
function flushPaymentNotes() {
    var saves = [];
    $('.serviceBlockComment').each(function () { saves.push(autoSaveServiceBlockComment($(this))); });
    return $.when.apply($, saves);
}
function paymentRequest(data) {
    data.customer_id = paymentCustomerId();
    return $.ajax({url: 'addPayment.php', type: 'POST', dataType: 'json', data: data});
}

$(function () {
    if (typeof recalculatePaymentSystem === 'function') recalculatePaymentSystem();
    paymentRows().each(function () {
        var $row = $(this);
        if (Number($row.find('.row-id').val())) $row.data('saved-payment', paymentSnapshot(paymentRowData($row)));
    });
    $(document).on('input change', '.serviceDiscountDisplay', function () {
        var $row = $(this).closest('tr');
        if (!$row.attr('data-settlement-kind') || $row.hasClass('row-reconciled-locked')) return;
        $row.find('.serviceDiscountAmount').val(paymentRowData($row).discount);
        recalculatePaymentSystem();
        triggerPaymentAutoSave();
    });
    $(document).on('click.paymentLedger', '.infoDelete, .removeNewPaymentRow', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var $row = $(this).closest('tr');
        if ($row.hasClass('row-reconciled-locked') || $row.find('[data-tds-locked="1"], [data-reconciled="1"]').length) {
            alert('Cannot delete a verified/reconciled payment.'); return;
        }
        if (!confirm('Delete this payment row?')) return;
        paymentMutation(function () {
            var row = paymentRowData($row);
            if (!row.id) { $row.remove(); recalculatePaymentSystem(); return; }
            return paymentRequest({action:'deletePayment', payment_id:row.id, version:row.version}).done(function (res) {
                if (!res || !res.success) { alert(res && res.message || 'Delete failed.'); return; }
                $row.remove(); recalculatePaymentSystem(); showAutoSaveIndicator('saved', 'Deleted');
            }).fail(function () { showAutoSaveIndicator('error', 'Delete failed'); });
        });
    });
    window.addEventListener('beforeunload', function (event) {
        var dirty = isPaymentSaving || paymentMutationPending || !!paymentAutoSaveTimer;
        paymentRows().each(function () { if (paymentRowNeedsSave($(this), paymentRowData($(this)))) dirty = true; });
        $('.serviceBlockComment').each(function () {
            var saved = $(this).data('saved-note');
            if (saved === undefined) saved = this.defaultValue || '';
            if ($(this).data('note-promise') || $(this).val() !== saved) dirty = true;
        });
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });
    $(document).on('click', '.retryPaymentSave', function () { triggerPaymentAutoSave(true); flushPaymentNotes(); });
});

const fs = require('fs'), assert = require('assert'), {spawnSync} = require('child_process');
const {JSDOM} = require(require('path').join(process.env.TEMP, 'aitf-payment-tests/node_modules/jsdom'));
const source = fs.readFileSync('customerAccount.php', 'utf8');
const start = source.indexOf("$recRaw = trim(strtolower($payInfo['reconciliation']");
const end = source.indexOf('</tr>', start) + 5;
assert.ok(start > 0 && end > start);
// Render the actual saved-row PHP markup for every combination, without connecting to a DB.
function render(payment, tds) {
    const record = Buffer.from(JSON.stringify({payment_id:1, reconciliation:payment?'Verified':'Verify', tds_verified:tds?'Verified':'unverified',
        payment_received:'2000.00', tds_amount:'100.00', discount_amount:0, payment_date:'2026-10-05', cheque_details:'NEFT', row_version:0, service_type:'CA'})).toString('base64');
    const php = `<?php require 'include/payment_ledger.php'; $payInfo=json_decode(base64_decode('${record}'),true);
        $isDiscountRow=false; $isSettlement=false; $displayRcv='2000.00'; $displayTds='100.00'; $displayDiscount=''; $svcName='CA';
        ${source.slice(start, end)}`;
    const result = spawnSync('C:/xampp/php/php.exe', [], {input:php, encoding:'utf8'});
    assert.equal(result.status, 0, result.stderr);
    assert.equal(result.stderr, '');
    return result.stdout;
}
const dom = new JSDOM('<div id="service_blocks_container" data-customer="380"><div class="service-payment-block" data-service="CA"><table class="service-details-table"><tbody></tbody></table></div></div>', {runScripts:'outside-only',url:'http://localhost/'});
const w=dom.window; w.eval(fs.readFileSync('js/vendor/jquery.min.js','utf8')); const $=w.jQuery;
w.alert=()=>{}; w.recalculatePaymentSystem=()=>{}; w.showAutoSaveIndicator=()=>{};
const requests=[];
$.ajax=options=>{const deferred=$.Deferred(); requests.push({options,deferred}); return deferred.promise();};
w.eval(fs.readFileSync('js/payment-ledger.js','utf8'));
assert.ok(!source.includes('toggleReconcileBtn'));
assert.ok(!source.includes("action:'updateReconcileStatus'"));
const templateStart=source.indexOf('        function recalculatePaymentSystem()');
const templateEnd=source.indexOf('        // Add Invoice Header Button Click Handler',templateStart);
w.eval(source.slice(templateStart,templateEnd));
function verifyFields($row,payment,tds) {
    assert.equal($row.find('.servicePaymentReceived').prop('readonly'),payment);
    assert.equal($row.find('.serviceTdsAmount').prop('readonly'),tds);
    assert.equal($row.find('.payment-verified-icon').length,payment?1:0);
    assert.equal($row.find('.tds-verified-icon').length,tds?1:0);
    assert.equal($row.find('.paymentDepositDate').prop('readonly'),payment||tds);
    assert.equal($row.find('.serviceChequeDetails').prop('readonly'),payment||tds);
    assert.equal($row.hasClass('row-reconciled-locked'),payment&&tds);
}
(async()=>{
    await new Promise(r=>setTimeout(r,20));
    for (const payment of [false,true]) for (const tds of [false,true]) {
        $('tbody').html(render(payment,tds)); const $row=$('tr');
        verifyFields($row,payment,tds);
        $row.data('saved-payment',w.paymentSnapshot(w.paymentRowData($row)));
        const $status=$row.find('.paymentReconcileStatus');
        assert.equal($status.prop('tagName'),'SPAN');
        assert.equal($status.attr('role'),'status');
        assert.equal($status.find('.fa-check').length,payment?1:0);
        if (!payment) assert.equal($status.text().trim(),'Pending');
        $status.trigger('click').trigger($.Event('keydown',{key:'Enter'}));
        assert.equal(requests.length,0);
        assert.equal($row.find('.serviceReconciliation').val(),payment?'Verified':'Verify');
        verifyFields($row,payment,tds);
        if (!tds) {
            $row.find('.serviceTdsAmount').val('125');
            w.executePaymentAutoSave();
            const save=requests.shift(), data=JSON.parse(save.options.data.rows)[0];
            assert.equal(data.tds,'125'); assert.equal(data.received,'2000.00'); assert.equal(data.version,0);
            save.deferred.resolve({success:true,rows:[{id:1,version:2}]});
        } else if (!payment) {
            $row.find('.servicePaymentReceived').val('2200');
            w.executePaymentAutoSave();
            const save=requests.shift(), data=JSON.parse(save.options.data.rows)[0];
            assert.equal(data.received,'2200'); assert.equal(data.tds,'100.00');
            save.deferred.resolve({success:true,rows:[{id:1,version:2}]});
        }
        assert.equal(requests.length,0);
    }
    $('#service_blocks_container').html(w.renderServiceBlockTemplate('CA',0,380,'',['CA'],'CA'));
    const $newStatus=$('.paymentReconcileStatus');
    assert.ok($newStatus.length>0);
    assert.equal($newStatus.prop('tagName'),'SPAN');
    assert.equal($newStatus.text().trim(),'Pending');
    $newStatus.trigger('click'); assert.equal(requests.length,0);
    console.log('PASS: All 4 verification states are display-only; new rows show Pending and independent amount edits autosave.');
    w.close();
})().catch(error=>{console.error(error);process.exit(1);});

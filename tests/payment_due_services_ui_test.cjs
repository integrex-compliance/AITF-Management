const fs = require('fs'), path = require('path'), assert = require('assert');
const {execFileSync} = require('child_process');
const {JSDOM} = require(path.join(process.env.TEMP, 'aitf-payment-tests/node_modules/jsdom'));
const source = fs.readFileSync('paymentPage.php', 'utf8');
// Render the real table template without loading the account page's unrelated hooks.
const render = `
$source=file_get_contents('paymentPage.php');
$base=['id'=>42,'customer_name'=>'Example & Co','contact_person'=>'Accounts','contact'=>'123','email_id'=>'test@example.test','total_inv'=>100,'total_paid'=>20,'total_tds'=>5,'balance'=>75,'comment'=>'Existing note','reminder_count'=>2,'service_type'=>'Same label'];
$allPaymentInfo=[];
foreach (['CA','CA (2)'] as $index=>$header) {
    $row=$base; $row['header_key']=$header;
    $row['invoices']=[['id'=>100+$index,'number'=>'CD/2026/JAN-'.(100+$index)]];
    $allPaymentInfo[]=$row;
}
$start=strpos($source,'<?php foreach ($allPaymentInfo');
$end=strpos($source,'</tbody>',$start);
eval('?>'.substr($source,$start,$end-$start));
`;
const rows = execFileSync('C:/xampp/php/php.exe',['-r',render],{encoding:'utf8'});
const modals = source.slice(source.indexOf('<div id="paymentInvoicePreviewModal"'), source.indexOf('<script type="text/javascript">'));
const dom = new JSDOM('<input id="paymentClientSearch"><input id="minAmount"><input id="maxAmount"><table id="tab_customer_list"><tbody>'+rows+'</tbody></table>'+modals, {runScripts:'outside-only',url:'http://localhost/public_html/paymentPage.php'});
const w=dom.window;
w.eval(fs.readFileSync('js/vendor/jquery.min.js','utf8'));
const $=w.jQuery, requests=[];
const table={draw(){return this;},search(){return this;},columns:{adjust(){return table;}}};
$.fn.DataTable=function(){return table;}; $.fn.DataTable.ext={search:[]};
$.fn.modal=function(action){this.attr('data-modal-action',action);return this;};
$.ajax=options=>requests.push(options); // No emails or external requests are sent.
const script=source.slice(source.indexOf('<script type="text/javascript">')+31,source.lastIndexOf('</script>')).replace(/<\?php[\s\S]*?\?>/g,'"fixture bank"');
w.eval(script);
(async()=>{
    await new Promise(resolve=>setTimeout(resolve,30));
    const filter=$.fn.DataTable.ext.search[0];
    const settings={nTable:$('#tab_customer_list')[0],aoData:[{nTr:$('#tab_customer_list tbody tr')[0]}]};
    const data=['Example & Co (Accounts)','Same label','CD/2026/JAN-100','20.00','75.00'];
    $('#paymentClientSearch').val('example &'); assert.equal(filter(settings,data,0),true);
    $('#paymentClientSearch').val('Same label'); assert.equal(filter(settings,data,0),false);
    $('#paymentClientSearch').val(''); $('#maxAmount').val('0'); assert.equal(filter(settings,data,0),false);
    $('#maxAmount').val('75'); $('#minAmount').val('75'); assert.equal(filter(settings,data,0),true);
    $('#maxAmount,#minAmount').val('');
    const ids=$('#tab_customer_list tbody tr').map((i,row)=>row.id).get();
    assert.equal(new Set(ids).size,2,'Rows for one customer have unique IDs');
    assert.equal($('.payment-invoice-preview').length,2);
    assert.equal($('.payment-invoice-preview').first().text(),'CD/2026/JAN-100');
    $('.payment-invoice-preview').eq(1).trigger('click');
    assert.equal($('#paymentInvoicePreviewModal').attr('data-modal-action'),'show');
    assert.equal($('#paymentInvoicePreviewTitle').text(),'CD/2026/JAN-101');
    assert.ok($('#paymentInvoicePreviewFrame').attr('src').endsWith('invoiceid=101&invoiceCustomerId=42'));
    assert.equal($('#paymentInvoicePreviewLink').attr('href'),$('#paymentInvoicePreviewFrame').attr('src'));
    $('#paymentInvoicePreviewModal').trigger('hidden.bs.modal');
    assert.equal($('#paymentInvoicePreviewFrame').attr('src'),undefined);
    $('.btnSendReminderMail').eq(1).trigger('click');
    assert.equal($('#modalReminderDueAmount').text(),'75.00');
    $('#btnConfirmSendDueReminder').trigger('click');
    const reminder=requests.shift();
    assert.equal(reminder.url,'sendPaymentDueReminder.php');
    assert.equal(reminder.data.customerId,'42');
    assert.equal(reminder.data.headerKey,'CA (2)','Reminder uses internal key, not shared label');
    reminder.success({success:true,loggedNote:'Reminder email sent for CA (2)'});
    $('.reminder-count-val').each((i,el)=>assert.equal($(el).text(),'3'));
    $('.saveChanges').each((i,el)=>assert.equal($(el).val(),'Existing note\nReminder email sent for CA (2)'));
    $('.saveChanges').first().val('Updated follow-up').trigger('change');
    const note=requests.shift(); assert.equal(note.data.serviceType,'CA'); note.success({status:'success'});
    $('.saveChanges').each((i,el)=>assert.equal($(el).val(),'Updated follow-up'));
    console.log('PASS: invoice rendering, inline preview, exact-header reminders and repeated customer row synchronization.');
    w.close();
})().catch(error=>{console.error(error);w.close();process.exitCode=1;});

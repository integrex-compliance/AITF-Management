
var jsdom = require('jsdom');
var { JSDOM } = jsdom;
var dom = new JSDOM(
<html>
<body>
<input type='text' class='assessmentImplementationDate' value='25.05.2026'>
<div id='qmsImplementationDateError'><span id='qmsImplementationDateErrorMsg'></span></div>
</body>
</html>
);
global.window = dom.window;
global.document = dom.window.document;
var $ = require('jquery')(dom.window);
global.$ = $;
global.jQuery = $;

var fs = require('fs');
var jsCode = fs.readFileSync('js/date-chronology-validation.js', 'utf8');
eval(jsCode);

DateChronologyValidator.init({
    customerId: '82',
    typedServiceId: '200059',
    serviceType: 'CA',
    fields: {
        'qms_implementation_date': { input: '.assessmentImplementationDate', errorDiv: '#qmsImplementationDateError', errorMsg: '#qmsImplementationDateErrorMsg' }
    },
    submitBtn: '.addAssessment'
});

setTimeout(function() {
    console.log('Validating...');
    .assessmentImplementationDate.trigger('change');
    console.log('Error text:', #qmsImplementationDateErrorMsg.text());
    console.log('Error div visible:', #qmsImplementationDateError.css('display'));
}, 500);


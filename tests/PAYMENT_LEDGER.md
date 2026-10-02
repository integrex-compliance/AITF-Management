Payment ledger regression checks
===============================

Run from the project root on local XAMPP:

```powershell
C:\xampp\php\php.exe tests/payment_ledger_test.php
npm install --prefix "$env:TEMP\aitf-payment-tests" jsdom@26 --no-audit --no-fund
node tests/payment_ledger_ui_test.cjs
```

The PHP suite uses connection-local temporary tables and customer 380's invoice 1760 as a fixture. Test writes do not change customer data. The UI suite exercises repository jQuery, the actual service-block template and header controls in jsdom with controlled AJAX responses; it is not a full authenticated browser test.

Current flow
------------

- Invoice editing shows existing headers in the Move dropdown.
- The last dropdown option, + Add new header, opens the service list. Done saves the header and selects it in the Move dropdown without closing or resetting the invoice form. Other supports custom names.
- Selecting a target does not move anything. Move changes only the invoice; receipts are entered or reassigned manually.
- Header creation is idempotent and creates no zero-value payments.
- Completely empty/zero-only headers are hidden on reload and removed from the page after successful saves/deletes. A new header remains available during the current editing visit so an invoice can be moved into it. Header metadata and financial records are not bulk-deleted by cleanup.
- Any actual invoice, nonzero amount, date, instrument, note or verification keeps its header. Date-only and instrument-only drafts are saved even before an amount is entered. Unsaved changes and failed saves are protected from cleanup.
- The invoice link icon, matching dialog and automatic receipt mapping are removed. Old mapping columns are inert; existing financial records remain as users saved them.

Coverage includes blank rows, retries, save/delete ordering, verification locks, stale edits, rollback, notes, header creation/rename, duplicate creation, failed requests, and ensuring invoice moves leave receipts unchanged, including formerly linked receipts.

The optional payment_customer_audit.php reads historical client-380 grouping and applies additive schema changes. It makes no financial data changes. Its original-data expectations may no longer apply after intentional edits by users.

Deployment includes customerAccount.php, modalForms.php, addPayment.php, moveInvoiceHeader.php, include/schema_auto_sync.php, include/payment_ledger.php, include/payment_ledger_actions.php, js/payment-ledger.js and js/invoice-header-controls.js. Remove the retired include/invoice_payment_mapping.php and js/invoice-payment-mapping.js files. Reload open account pages after deployment.

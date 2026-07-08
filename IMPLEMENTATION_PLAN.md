# Supplier/Creditor Management Implementation Plan

## Scope and non-negotiables

This plan introduces supplier/creditor management without changing the existing certification client flow. The current Add New Client modal, `addCustomer.php`, Clients module, certification logic, ISO standards, quotation flow, proforma invoices, final invoices, and client payment logic must remain functionally unchanged.

Implementation must be additive and backward compatible. Do not run destructive SQL such as dropping tables, renaming existing columns, truncating data, or deleting legacy vendor values.

## Current system analysis

The ERP is a PHP/XAMPP application using shared top-level PHP pages, modal includes, jQuery/Bootstrap-style UI behavior, and a MySQL database named `aitf_web` through `dbConnect.php`.

`header.php` is included by most authenticated ERP pages and performs role gating, sidebar rendering, the top navbar, global client search, and the current Add New Client button. The top navbar button is:

- selector/class: `.addAccountCss.modern-add-btn`
- label: `Add New Client`
- behavior: jQuery click handler in `header.php` opens `#customerAdd`
- modal source: `modalForms.php`
- submit target: `addCustomer.php`

The Add New Client flow currently posts to `addCustomer.php`, inserts or updates `customer_info`, and redirects to `customerAccount.php?customerid=...`. It also has a local API sync block. This flow should not be reused for suppliers and should not be modified except for changing how the header exposes the existing trigger.

Clients are certification entities stored in `customer_info` and connected to service/certification/payment/invoice tables. Suppliers should not be inserted into `customer_info` because doing so would pollute certification, IAF, quotations, agreement, invoice, payment, TDS, and client search behavior.

`billsPage.php` currently owns its bill/vendor database bootstrap, business logic, UI, and JavaScript in one file. It creates the `bills` and `vendors` tables if missing, accepts manual vendor names, auto-creates vendor rows, stores payment status directly on the bill, and stores a single bill attachment path on the bill row.

Current bill flow is coupled: bill creation/edit includes `vendor_name`, `amount`, `due_date`, `paid_amount`, `payment_status`, `description`, and `attached_file`. Payment status is partly user-editable and partly recalculated from `paid_amount`.

Current vendor handling is text-based:

- `vendors.vendor_name` is the primary key.
- `bills.vendor_name` is a plain string.
- `billsPage.php` joins vendors to bills by name.
- new bill creation auto-creates a vendor if missing.
- vendor details are edited in a popup inside `billsPage.php`.

Current client payment logic is separate:

- `addPayment.php` writes to `payment_system_id` and `payment_system`.
- `paymentPage.php` lists client receivables from `payment_system` joined to `customer_info`.
- `invoice_details.invoice_status` is toggled by `addPayment.php` for received/not received.
- This logic is for certification clients and should not be used for supplier bills.

Current TDS module is client-facing:

- Sidebar item: `TDS DEDUCTED` links to `listOfTds.php`.
- `listOfTds.php` selects `payment_system` rows where `ps.service_type LIKE '%tds%'`, joined to `customer_info` and latest `service`.
- Report helpers: `include/tds_report_lib.php`, `include/tds_report_pdf.php`, `exportTdsReport.php`, `exportTdsReportExcel.php`, `emailTdsReport.php`.
- The existing TDS report is about TDS deducted from client payments/receipts and should not be duplicated or contaminated with supplier payable TDS unless a later requirement explicitly asks for a unified report.

Live read-only schema inspection confirmed these current relevant tables:

- `bills`: `bill_id`, `vendor_name`, `amount`, `due_date`, `paid_amount`, `payment_status`, `description`, `attached_file`, `created_at`, `updated_at`
- `vendors`: `vendor_name`, `phone`, `email`, `gst_no`
- `customer_info`: client/certification master table with `id`, `customer_name`, `unique_id`, `gstin_no`, `address`, `contact_person`, `contact`, `email_id`, `scope`, certification fields, and `is_deleted`
- `payment_system`: client payment/TDS rows with `payment_id`, `payment_sy_id`, `customer_id`, `service_type`, `amount_decided`, `payment_received`, `payment_date`, `cheque_details`, `balance`, `comment`
- `payment_system_id`: client payment batch header
- `invoice_details`, `invoice_services`, `service`: client invoice/service tables

Current data observed:

- `bills`: 1 row
- `vendors`: 1 row
- sample bill: bill id `1`, vendor name `testing bill`, amount `270000.00`, due date `2026-06-30`, paid amount `0.00`, status `Unpaid`

## Existing files involved

Core shared/UI files:

- `header.php`: role checks, sidebar, top navbar Add New Client button, client search, Add New Client click handler
- `modalForms.php`: existing `#customerAdd` client modal and validation glue
- `addCustomer.php`: existing client create/update handler
- `utility.php`: role helper methods
- `dbConnect.php`: PDO connection to `aitf_web`
- `head.php`: shared assets

Bills/vendor files:

- `billsPage.php`: existing bills UI, vendor popup, bill CRUD, inline migrations, status calculation, upload handling
- `exportBillsReport.php`: existing vendor bills PDF report using `bills.vendor_name`, `paid_amount`, `payment_status`

Client payment and TDS files to inspect/protect:

- `addPayment.php`: existing client payment insert/update/delete and invoice received toggles
- `paymentPage.php`: client payment pending report
- `listOfTds.php`: existing TDS DEDUCTED list
- `include/tds_report_lib.php`
- `include/tds_report_pdf.php`
- `exportTdsReport.php`
- `exportTdsReportExcel.php`
- `emailTdsReport.php`

Client/certification modules to avoid changing unless only adding links:

- `homePage.php`
- `clients.php`
- `customerAccount.php`
- `quotationPage.php`
- `invoicePage.php`
- `agreementPage.php`
- certification/audit pages and modal files included by `header.php`

## Existing database tables involved

Do not alter behavior for:

- `customer_info`
- `service`
- `service_type_ca`, `service_type_sai`, `service_type_saii`, `service_type_rc`, `service_type_sp`
- `quotation`/audit agreement-related tables used by `quotationPage.php` and `agreementPage.php`
- `invoice_details`
- `invoice_services`
- `payment_system`
- `payment_system_id`

Existing bill/vendor tables to migrate additively:

- `bills`
- `vendors`

The `vendors` table should become legacy/read-only for migrated records. New supplier records should live in new supplier tables.

## Proposed architecture

Use a separate supplier domain, not the client domain.

Recommended design:

- `suppliers`: one master table with `supplier_type`, shared identity/contact/tax/status/opening balance fields.
- Type-specific detail tables for normalized fields that do not apply to every supplier:
  - `supplier_employee_details`
  - `supplier_contractor_details`
  - `supplier_goods_details`
- `supplier_bank_accounts`: normalized bank details, one or more bank accounts per supplier.
- `supplier_attachments`: multiple documents per supplier.
- Existing `bills` table extended with `supplier_id`, `bill_number`, `bill_date`, and legacy metadata while retaining `vendor_name`.
- `bill_payments`: multiple payment records per bill.
- Optional `bill_payment_attachments`: separate table if multiple proofs per payment are needed. If only one proof is required now, a `payment_proof_file` column on `bill_payments` is acceptable.

Rationale:

- A single supplier master table with `supplier_type` is appropriate for common search/listing, dropdowns, outstanding summaries, and foreign keys from bills.
- Type-specific tables avoid forcing employee-only, contractor-only, and goods-supplier-only columns into one wide nullable table.
- Attachments should be separate because each supplier type requires multiple documents and document metadata.
- Bank accounts should be separate because bank details may be absent, changed, or more than one account may be needed.
- Existing `bills` should be extended instead of replaced so current report/page URLs and legacy records remain compatible.

## Supplier type forms and fields

All supplier forms should include a shared section:

- Supplier type: `Employee`, `Contractor / Service Provider`, `Goods Supplier`
- Display/legal name
- PAN
- Contact person, where applicable
- Contact number
- Email
- Address
- City/state/postcode/country, or one address textarea matching current app style
- Status: Active/Inactive
- Opening balance and opening balance date
- Notes

Bank section, where applicable:

- Account holder name
- Bank name
- Branch
- Account number
- IFSC
- Account type
- UPI ID, optional
- Primary account flag

Employee supplier form:

- Employee code
- Department
- Designation
- Joining date
- Employment type
- PAN required
- GSTIN not required
- Bank details usually required
- Common documents:
  - PAN card
  - Aadhaar/identity proof
  - Bank proof/cancelled cheque
  - Appointment/contract letter
  - Reimbursement support documents

Contractor / Service Provider form:

- Business/proprietor/firm name
- Contact person
- Service category
- Contract start/end date
- GST registration status
- GSTIN required only when GST registered
- MSME/Udyam number, optional
- TDS section 194C/194J/other default, optional
- Default TDS rate, optional
- Common documents:
  - PAN card
  - GST certificate if registered
  - Service agreement/work order
  - MSME/Udyam certificate, if any
  - Bank proof
  - Compliance documents relevant to service

Goods Supplier form:

- Business/legal name
- Contact person
- Product/category supplied
- GST registration status
- GSTIN required when GST registered
- State and place of supply
- Payment terms
- MSME/Udyam number, optional
- Common documents:
  - PAN card
  - GST certificate if registered
  - Trade license, if any
  - Product/catalog/compliance certificates, if any
  - Bank proof
  - Purchase terms/agreement

GSTIN validation rule:

- Employee: hidden or explicitly Not Applicable.
- Contractor/Goods: show GST registration selector. Require GSTIN only when `gst_registration_status = Registered`.
- Existing client GSTIN logic must not be reused if it changes client validation behavior.

## Database schema changes

All schema changes should be additive and applied through a reviewed migration script, not silent page-load migrations in `billsPage.php`.

Recommended new tables:

```sql
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_type ENUM('employee','contractor','goods') NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255) NULL,
    pan_no VARCHAR(20) NULL,
    gst_registration_status ENUM('not_applicable','unregistered','registered') NOT NULL DEFAULT 'unregistered',
    gstin VARCHAR(20) NULL,
    contact_person VARCHAR(255) NULL,
    contact_no VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postcode VARCHAR(20) NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'India',
    opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    opening_balance_date DATE NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    legacy_vendor_name VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_type (supplier_type),
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_supplier_pan (pan_no),
    INDEX idx_supplier_gstin (gstin),
    INDEX idx_supplier_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```sql
CREATE TABLE supplier_employee_details (
    supplier_id INT PRIMARY KEY,
    employee_code VARCHAR(100) NULL,
    department VARCHAR(150) NULL,
    designation VARCHAR(150) NULL,
    joining_date DATE NULL,
    employment_type VARCHAR(100) NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```sql
CREATE TABLE supplier_contractor_details (
    supplier_id INT PRIMARY KEY,
    service_category VARCHAR(150) NULL,
    contract_start_date DATE NULL,
    contract_end_date DATE NULL,
    msme_no VARCHAR(100) NULL,
    default_tds_section VARCHAR(50) NULL,
    default_tds_rate DECIMAL(5,2) NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```sql
CREATE TABLE supplier_goods_details (
    supplier_id INT PRIMARY KEY,
    product_category VARCHAR(150) NULL,
    place_of_supply VARCHAR(150) NULL,
    payment_terms VARCHAR(150) NULL,
    msme_no VARCHAR(100) NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```sql
CREATE TABLE supplier_bank_accounts (
    bank_account_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    account_holder_name VARCHAR(255) NULL,
    bank_name VARCHAR(255) NULL,
    branch_name VARCHAR(255) NULL,
    account_no VARCHAR(100) NULL,
    ifsc_code VARCHAR(20) NULL,
    account_type VARCHAR(50) NULL,
    upi_id VARCHAR(100) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_bank_supplier (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```sql
CREATE TABLE supplier_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    document_title VARCHAR(255) NULL,
    file_path VARCHAR(500) NOT NULL,
    original_file_name VARCHAR(255) NULL,
    mime_type VARCHAR(100) NULL,
    uploaded_by INT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_attachment_supplier (supplier_id),
    INDEX idx_attachment_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Recommended additive changes to `bills`:

```sql
ALTER TABLE bills ADD COLUMN supplier_id INT NULL AFTER bill_id;
ALTER TABLE bills ADD COLUMN bill_number VARCHAR(100) NULL AFTER supplier_id;
ALTER TABLE bills ADD COLUMN bill_date DATE NULL AFTER bill_number;
ALTER TABLE bills ADD COLUMN legacy_vendor_name VARCHAR(255) NULL AFTER vendor_name;
ALTER TABLE bills ADD INDEX idx_bills_supplier (supplier_id);
ALTER TABLE bills ADD INDEX idx_bills_bill_date (bill_date);
ALTER TABLE bills ADD CONSTRAINT fk_bills_supplier
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id);
```

Do not make `supplier_id` `NOT NULL` until after migration is complete and verified. Even then, keep legacy rows supported unless all rows are mapped.

Recommended payment table:

```sql
CREATE TABLE bill_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    supplier_id INT NOT NULL,
    payment_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_mode ENUM('cash','cheque','bank_transfer','upi','card','other') NOT NULL DEFAULT 'bank_transfer',
    account_reference VARCHAR(255) NULL,
    utr_number VARCHAR(100) NULL,
    tds_deducted DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    payment_proof_file VARCHAR(500) NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_bill_payments_bill (bill_id),
    INDEX idx_bill_payments_supplier (supplier_id),
    INDEX idx_bill_payments_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Optional if multiple payment proofs are needed:

```sql
CREATE TABLE bill_payment_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_file_name VARCHAR(255) NULL,
    mime_type VARCHAR(100) NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES bill_payments(payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## Header UI flow

Change the current `Add New Client` button UI into an `Add New` dropdown while preserving the existing client behavior.

Required behavior:

- The dropdown trigger label should be `Add New`.
- Option 1: `Add New Client`
  - Calls the existing `$("#customerAdd").modal("show")` behavior.
  - Keeps `.customerAdd` modal and `addCustomer.php` untouched.
- Option 2: `Add New Supplier`
  - Opens a supplier type selector modal/page.
  - Supplier type options: Employee, Contractor / Service Provider, Goods Supplier.

Implementation detail:

- Do not remove the existing `#customerAdd` modal.
- Either keep `.addAccountCss` on the Add New Client dropdown item or introduce a new specific class that calls the same modal.
- Add a new supplier modal in a separate include such as `supplierModalForms.php` or route to `supplierForm.php?type=...`.
- Keep the global client search box as client-only.

Mobile/sidebar:

- Add Supplier Management to the sidebar and mobile menu for roles that can manage finance/bills.
- Do not rename `CLIENTS`, `PAYMENT`, `TDS DEDUCTED`, `INVOICES`, or existing client menu labels.

## Supplier Management UI

Create `supplierManagement.php` or `suppliers.php` with `$pageIdentifier = "Suppliers"` and header role gating.

Recommended access:

- Main Admin
- Admin
- Office Staff
- Finance

Page features:

- Type filters: All, Employee, Contractor / Service Provider, Goods Supplier
- Status filter: Active/Inactive
- Search by supplier name, PAN, GSTIN, contact, email
- Summary cards:
  - total suppliers
  - total outstanding
  - overdue payable amount
  - bills due this week/month
- Listing columns:
  - supplier name
  - type
  - PAN
  - GSTIN or Not Applicable
  - contact/email
  - outstanding balance
  - status
  - actions
- Actions:
  - View
  - Edit
  - Bills
  - Ledger
  - Documents

View/Edit:

- Shared supplier fields
- Type-specific section
- Bank accounts section
- Attachments section
- Audit metadata where available

Documents:

- Multiple upload support.
- Document type dropdown varies by supplier type.
- Store under a new folder such as `uploads/suppliers/`.
- Allowed types should match or extend bill upload policy: PDF, JPG, JPEG, PNG. Consider DOC/DOCX/XLS/XLSX only if business requires it.

## Bills page changes

`billsPage.php` should become supplier-driven while keeping existing records visible.

Required changes:

- Replace UI labels `Vendor Name` with `Supplier`.
- Remove manual vendor entry for new/edited supplier-linked bills.
- Supplier field must be a dropdown/searchable select populated from active registered suppliers.
- New bills must store `supplier_id`.
- Existing `vendor_name` values remain for legacy rows and display as legacy suppliers until migrated.
- New bill creation must not auto-create a vendor/supplier.

Bill creation fields:

- Supplier
- Bill Number
- Bill Date
- Bill Amount
- Due Date
- Description
- Bill Attachment

New bill defaults:

- `payment_status = 'Unpaid'`
- `paid_amount = 0.00`
- no payment record created

Edit bill:

- Allow changing Supplier only if no payments exist, or require explicit confirmation/audit handling.
- Do not allow manually editing paid amount or payment status.
- Attachment replacement should preserve current behavior but should not delete old files until replacement is confirmed.

Listing changes:

- Join `bills.supplier_id` to `suppliers.supplier_id`.
- For migrated/linked records, display `suppliers.supplier_name`.
- For unmigrated legacy records where `supplier_id IS NULL`, display `vendor_name` with a `Legacy Vendor` badge.
- Search should include supplier name, legacy vendor name, bill number, description.
- Status should be calculated from bill amount and payment totals.

Reports:

- Update `exportBillsReport.php` to join suppliers and show `Supplier`.
- Preserve legacy `vendor_name` report output for unmapped legacy bills.
- Rename report titles from Vendor Bills Report to Supplier Bills Report only after UI migration.

## Payment flow

Separate bill creation from payment recording.

Create a Record Payment flow launched from bill row actions and supplier bill detail page.

Payment fields:

- amount
- date
- payment mode
- account/bank reference
- transaction/UTR number
- TDS deducted
- notes
- payment proof upload

Business rules:

- Multiple payments per bill are allowed.
- Payment amount must be positive.
- Total cash/bank paid plus TDS deducted should not exceed bill amount unless overpayment is explicitly allowed.
- For outstanding calculation, use:
  - paid total = sum of `bill_payments.payment_amount`
  - TDS total = sum of `bill_payments.tds_deducted`
  - settled total = paid total + TDS total
  - outstanding = bill amount - settled total
- Status is calculated:
  - `Unpaid`: settled total = 0
  - `Partially Paid`: settled total > 0 and < bill amount
  - `Paid`: settled total >= bill amount
- Existing `bills.paid_amount` and `bills.payment_status` may be maintained as cached/backward-compatible columns during transition but must be derived from `bill_payments`, not user-entered.

Backfilling existing paid amounts:

- For legacy rows with `paid_amount > 0`, create one migration payment row marked as `mode = other`, note = `Migrated from bills.paid_amount`.
- Current observed bill has `paid_amount = 0.00`, so no payment backfill is needed for that row.

## TDS integration strategy

Do not duplicate or alter the existing client TDS DEDUCTED module behavior.

Existing TDS module is tied to `payment_system`, `customer_info`, and client services. Supplier payable TDS should be represented in `bill_payments.tds_deducted`.

Recommended supplier-side TDS behavior:

- Store TDS deducted per supplier bill payment.
- Show TDS deducted in bill payment history and supplier ledger.
- Include TDS in supplier outstanding calculation as a settlement component.
- Add a future `supplierTdsReport.php` only if finance requires payable-side TDS reports.
- Do not insert supplier TDS into `payment_system`.
- Do not make `listOfTds.php` read supplier payment rows unless a later approved requirement asks for a combined TDS report with separate client/supplier sections.

Optional future normalized table:

```sql
CREATE TABLE supplier_tds_entries (
    tds_entry_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    bill_id INT NOT NULL,
    payment_id INT NULL,
    tds_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tds_section VARCHAR(50) NULL,
    tds_rate DECIMAL(5,2) NULL,
    deduction_date DATE NOT NULL,
    certificate_no VARCHAR(100) NULL,
    certificate_file VARCHAR(500) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
    FOREIGN KEY (payment_id) REFERENCES bill_payments(payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Do not add this table in phase 1 unless reporting/certificate tracking is needed immediately. `bill_payments.tds_deducted` is sufficient for bill settlement.

## Supplier ledger calculation

Supplier ledger should be derived, not manually edited.

Ledger sources:

- Opening balance from `suppliers.opening_balance`
- Bills from `bills` where `supplier_id = supplier.supplier_id`
- Payments from `bill_payments.payment_amount`
- TDS from `bill_payments.tds_deducted`

Ledger rows:

- Opening balance: debit/payable if positive
- Bill created: debit/payable increases by bill amount
- Payment recorded: credit/payable decreases by payment amount
- TDS deducted: credit/payable decreases by TDS amount

Running balance:

```text
balance = opening_balance
balance += bill_amount
balance -= payment_amount
balance -= tds_deducted
```

Supplier outstanding:

```text
opening_balance
+ SUM(bills.amount)
- SUM(bill_payments.payment_amount)
- SUM(bill_payments.tds_deducted)
```

Bill outstanding:

```text
bills.amount
- SUM(bill_payments.payment_amount)
- SUM(bill_payments.tds_deducted)
```

Ledger page actions:

- date range filter
- export PDF/Excel later if required
- drill-down links to bill and payment proof

## Existing bill/vendor migration strategy

Migration must be backward compatible and reversible at the application level.

Phase 1: additive schema

- Create supplier tables.
- Add nullable `supplier_id`, `bill_number`, `bill_date`, `legacy_vendor_name` to `bills`.
- Create `bill_payments`.
- Keep `vendor_name`, `paid_amount`, and `payment_status` columns.

Phase 2: legacy vendor mapping

- For each row in `vendors`, create a supplier record:
  - `supplier_type = 'contractor'` by default, unless business manually classifies it.
  - `supplier_name = vendors.vendor_name`
  - `contact_no = vendors.phone`
  - `email = vendors.email`
  - `gstin = vendors.gst_no`
  - `gst_registration_status = registered` only if GSTIN is present, otherwise `unregistered`
  - `legacy_vendor_name = vendors.vendor_name`
- For each `bills` row:
  - copy current `vendor_name` to `legacy_vendor_name`
  - set `supplier_id` by exact match to `suppliers.legacy_vendor_name`
  - preserve `vendor_name`
  - if `bill_date` is empty, use `DATE(created_at)` as a migration fallback
- For each bill with `paid_amount > 0`, create one `bill_payments` row:
  - `payment_amount = paid_amount`
  - `payment_date = DATE(updated_at)` or `DATE(created_at)` fallback
  - `payment_mode = 'other'`
  - note = `Migrated from legacy paid_amount`

Phase 3: application cutover

- New bill form requires supplier dropdown.
- Auto-create vendor logic is removed/disabled for new submissions.
- Vendor popup is replaced by supplier profile link.
- Legacy fallback remains for any bill without `supplier_id`.

Phase 4: verification

- Compare old totals:
  - `SUM(bills.amount)`
  - `SUM(bills.paid_amount)`
  - `SUM(bills.amount - bills.paid_amount)`
- Compare new derived totals:
  - `SUM(bills.amount)`
  - `SUM(bill_payments.payment_amount + bill_payments.tds_deducted)`
  - derived outstanding
- Totals must match for migrated rows before hiding legacy payment status controls.

No destructive cleanup:

- Do not drop `vendors`.
- Do not drop `bills.vendor_name`.
- Do not drop `bills.paid_amount` or `bills.payment_status` in the initial implementation.

## Backward compatibility risks

Client logic risk:

- `header.php` is shared by almost every authenticated page. A dropdown change must preserve `#customerAdd` behavior and avoid breaking global client search.

Bill total risk:

- Existing reports use `bills.paid_amount` and `bills.payment_status`. During transition, keep these fields synchronized from payment records or update all report reads to derived payment sums.

Legacy vendor risk:

- Current `vendors.vendor_name` is the primary key and may contain inconsistent names. Automated exact-match migration is safe for the current table but manual review is needed before merging duplicate suppliers.

TDS semantic risk:

- Existing TDS DEDUCTED reports are client-side receivable TDS. Supplier TDS is payable-side withholding. Mixing both without clear labeling would create finance/reporting confusion.

Date risk:

- Current `billsPage.php` and `exportBillsReport.php` contain hard-coded date context comments/values for overdue calculations. Implementation should replace hard-coded dates with `date('Y-m-d')` or a shared current-date helper.

Page-load migration risk:

- `billsPage.php` currently runs `CREATE TABLE`/`ALTER TABLE` on page load. New supplier schema should be migrated through explicit SQL scripts to avoid unexpected production changes during normal page views.

Permission risk:

- Supplier pages should follow the same role style as Bills/Finance. Do not expose supplier bank details/documents to client or external roles.

Upload risk:

- Supplier documents and payment proofs contain sensitive PAN/bank data. Store under controlled directories and validate MIME/extension. Avoid exposing directory indexes.

Foreign key risk:

- Adding a foreign key from `bills.supplier_id` must be nullable at first to support unmapped legacy rows.

## Step-by-step implementation phases

### Phase 0: approval and backup

1. Get explicit approval for implementation.
2. Take a database backup.
3. Confirm current production table counts and current `bills`/`vendors` data.
4. Create migration SQL scripts for review.

### Phase 1: schema

1. Create `suppliers`.
2. Create type detail tables.
3. Create `supplier_bank_accounts`.
4. Create `supplier_attachments`.
5. Add nullable supplier columns to `bills`.
6. Create `bill_payments`.
7. Do not alter existing client tables.

### Phase 2: supplier CRUD

1. Add supplier form/type selector.
2. Implement server-side supplier create/update handler, e.g. `saveSupplier.php`.
3. Add document upload handler and storage.
4. Add Supplier Management page and sidebar entry.
5. Implement View/Edit/Documents actions.

### Phase 3: header dropdown

1. Replace the current visible Add New Client button with Add New dropdown.
2. Add Add New Client item that opens existing `#customerAdd`.
3. Add Add New Supplier item that opens supplier type selector.
4. Test client add flow unchanged.

### Phase 4: bills integration

1. Refactor `billsPage.php` reads to join `suppliers` by `supplier_id`.
2. Change labels from Vendor to Supplier.
3. Replace manual vendor input/datalist with supplier dropdown.
4. Add bill number and bill date fields.
5. New bills insert with `supplier_id`, bill metadata, `paid_amount = 0`, `payment_status = Unpaid`.
6. Remove/disable auto-create vendor logic for new bills.
7. Keep legacy fallback display for old vendor-name-only bills.

### Phase 5: payment recording

1. Add Record Payment action.
2. Create payment modal/form.
3. Insert payments into `bill_payments`.
4. Recalculate bill cached `paid_amount` and `payment_status` after each payment if backward-compatible caches are retained.
5. Add payment history per bill.
6. Validate overpayment and TDS settlement rules.

### Phase 6: ledger

1. Build supplier ledger query/service.
2. Add Ledger action from Supplier Management and Bills.
3. Show opening balance, bills, payments, TDS, and running balance.
4. Add date filters.

### Phase 7: migration

1. Migrate existing `vendors` to `suppliers` using legacy name.
2. Map existing `bills.vendor_name` to `bills.supplier_id`.
3. Backfill `bill_date` from `created_at` where missing.
4. Backfill payment rows for any legacy `paid_amount > 0`.
5. Verify old and new totals.

### Phase 8: reports and polish

1. Update `exportBillsReport.php` to supplier terminology and derived payment totals.
2. Add supplier filters to bill report if needed.
3. Ensure dark mode styles remain readable.
4. Review responsive/mobile header dropdown.

## Testing checklist

Client flow preservation:

- Add New > Add New Client opens the same `#customerAdd` modal.
- Existing client form validation still works.
- `addCustomer.php` still inserts into `customer_info`.
- Client search still returns and opens `customerAccount.php`.
- Existing certification, quotation, invoice, agreement, client payment, and TDS pages load.

Supplier creation:

- Add New > Add New Supplier opens type selector.
- Employee form hides/does not require GSTIN.
- Contractor form requires GSTIN only when GST registered.
- Goods Supplier form requires GSTIN only when GST registered.
- PAN/contact/email/address save correctly.
- Bank details save correctly.
- Multiple attachments upload and list correctly.
- Supplier appears on Supplier Management page with correct type/status.

Supplier Management:

- Type filters work.
- Search by name/PAN/GSTIN/contact works.
- Outstanding balance is derived correctly.
- View/Edit/Bills/Ledger/Documents actions route correctly.
- Inactive suppliers do not appear in new bill dropdown unless explicitly included.

Bills:

- New bill requires registered supplier.
- Manual vendor entry is not available for new bills.
- Bill number/date/amount/due date/description/attachment save correctly.
- New bill starts Unpaid with zero payment.
- Existing legacy bill still displays.
- Legacy bill shows supplier if migrated, otherwise shows legacy vendor fallback.
- Bill report totals match UI totals.

Payments:

- Multiple payments can be recorded against one bill.
- Payment proof upload works.
- UTR/reference/mode/notes save correctly.
- TDS deducted saves per payment.
- Status automatically becomes Unpaid, Partially Paid, or Paid.
- Overpayment is blocked or explicitly handled.
- Cached `paid_amount`/`payment_status`, if retained, match derived totals.

TDS:

- Existing `listOfTds.php` output is unchanged for client TDS.
- Supplier TDS does not appear in client TDS report.
- Supplier ledger includes TDS as settlement.

Ledger:

- Opening balance is included.
- Bills increase payable balance.
- Payments decrease payable balance.
- TDS decreases payable balance.
- Running balance is correct by date order.

Migration:

- Every existing `vendors` row is represented in `suppliers` or explicitly logged for manual review.
- Existing `bills` rows keep `vendor_name`.
- Existing `bills` rows get `supplier_id` where matchable.
- Financial totals before and after migration match.
- No existing bill attachment paths are lost.

Security/permissions:

- Client/external roles cannot access supplier pages.
- Upload MIME/extension checks reject invalid files.
- Supplier bank/document pages are restricted to approved roles.

Regression:

- Header/sidebar render on desktop and mobile.
- `billsPage.php` works with empty suppliers table.
- `billsPage.php` works with legacy unmapped bills.
- Export PDFs do not throw warnings.
- No destructive SQL is required.

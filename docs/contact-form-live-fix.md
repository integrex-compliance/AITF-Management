# Contact form live PHP fix

The contact POST previously required `PHPMailer/PHPMailerAutoload.php`.
That directory is ignored by Git, so deploying tracked changes can leave an old
hosting copy in place. Old PHPMailer autoloaders declaring `__autoload` fail on
PHP 8: https://www.php.net/manual/en/function.autoload.php.
This is a likely explanation for a working local form and a live POST-only 500;
the exact production exception has not been confirmed from hosting logs.

The contact handler now uses the existing `aitfLoadPHPMailer()` loader and tracked
root classes. Catchable validation/mailer errors show a retry message and preserve
the form. Diagnostic details go to the PHP error log. A confirmation failure after
the office enquiry succeeds still reports receipt. User text in HTML mail is escaped.

## Deploy

Back up the corresponding live files, then extract the supplied ZIP into the live
`public_html` directory, preserving paths and replacing these four files:

- `contactUS.php`
- `include/invoice_mail_helper.php`
- `class.phpmailer.php`
- `class.smtp.php`

The ZIP uses the existing tracked helper and mailer classes. No `.env`, credentials,
test scripts, or database changes are included. Keep the site's existing environment
and captcha configuration. No PHP downgrade is needed for this loader change.

After deployment, submit a valid enquiry with captcha and check the office enquiry
and sender confirmation. If it fails, inspect the hosting PHP error log for
`CONTACT FORM FAIL`, `CONTACT MAIL SEND FAIL`, or `CONTACT CONFIRMATION SEND FAIL`.
These distinguish loading/runtime errors from SMTP delivery errors.

## Local verification

PHP 8.5.12 syntax validation passed. The local-only, Git-ignored test script
`tests/contact_form_regression.php` renders the real page with real PHPMailer MIME
generation and a fake SMTP transport; it never sends an email.

Passed scenarios: `success`, `fallback`, `smtp-fail`, `throw`,
`confirmation-fail`, `confirmation-throw`, `invalid`, `array-input`, `captcha`.
Example: `php tests/contact_form_regression.php confirmation-throw`.
The bundled legacy library emits non-fatal PHP 8.5 cast deprecation notices.
Live SMTP delivery and real Cloudflare verification were not exercised by these tests.

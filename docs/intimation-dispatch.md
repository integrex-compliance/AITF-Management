# Due-letter dispatch

`intimationLetter.php`, `sendIntimationLetter.php` and both scheduler entry points
use the same eligibility rules and the agreement/invoice mail transport. The
tracked root PHPMailer classes are loaded directly, avoiding obsolete autoloaders
left in ignored hosting folders after a PHP upgrade.

## Hosting schedule

Configure the host's scheduler to invoke this every five minutes, using the same
PHP version and extensions as the website:

```cron
*/5 * * * * /absolute/path/to/php /absolute/path/to/public_html/cron_auto_send_intimation_due.php >> /absolute/path/to/intimation-cron.log 2>&1
```

The script itself gates automatic delivery until **12:00 PM Asia/Kolkata**, so the
host's scheduler timezone need not match IST. Each run processes up to 100 rows
with a 240-second batch budget (an in-flight letter finishes before stopping).
Unattempted letters are prioritized before failed rows. Subsequent runs continue
the queue. The CLI exits nonzero on failure.

The existing `cron_auto_send_intimation_due.php` URL remains compatible when no
`AUTO_INTIMATION_TOKEN` is configured. If a token is configured, include it as
`?token=...` in an external URL scheduler. An authenticated staff session also
works. `autoSendDueIntimationLetters.php` retains token/local access support.
Both URLs execute the same runner; overlapping runs are locked out.

An open staff page provides a five-minute browser fallback. A server scheduler
is needed for unattended execution when nobody is using the website. The old
`last_due_mail_cron_date.txt` flag is no longer used; a failed browser request
cannot suppress all remaining attempts for the day.

Use `--dry-run` to list candidates without sending mail. `--limit=N` sets the
batch size (1–1000). `AUTO_INTIMATION_BACKLOG_DAYS` defaults to 30 and is bounded
to 0–90 days; `--backlog-days=N` provides a CLI override within those bounds.
Older letters remain in historical records but cannot be sent from the pending
queue or by manually posting their IDs. A later sent letter supersedes earlier
pending letters in the same service.

## Delivery and recovery

The first real send creates `intimation_mail_dispatch` (InnoDB); the database user
needs CREATE TABLE permission. A per-service MySQL advisory lock serializes cron
and Generate clicks. A durable acceptance receipt is saved before the transaction
that marks the letter sent and changes the client status. `customer_info` and
`ca_service_dates` must use InnoDB for that transaction; both local tables do.
The actual send date is stored. Reminders preserve client status; suspension,
withdrawal (both action spellings) and expiry letters apply the corresponding
status only after the mail transport accepts the message.

Ordinary SMTP/PDF/recipient failures remain retryable. An office-copy failure
does not resend the client email. If status synchronization fails after SMTP
acceptance, a retry uses the saved receipt and repairs the status without sending
again. SMTP acceptance does not establish inbox delivery.

Connection attempts now use `connecting`; interrupted attempts in that phase can
retry under the service lock because no message body has been transmitted.
Immediately before DATA, the state becomes `transmitting`. The SMTP acceptance
receipt is saved as soon as DATA is acknowledged, before waiting for QUIT. A
connection-close failure therefore cannot hide a confirmed acceptance. SMTP
command time limits and a 90-second limit on starting further connection
attempts prevent repeatedly starting slow fallbacks without a bound.

Older `sending` rows and interrupted `transmitting` rows have an unknown outcome.
Generate now offers a **Retry Letter** confirmation explaining that a duplicate
may result if the earlier attempt actually arrived. Confirming retries through
the normal mailer and status-update flow; no database edit is required. The
automatic scheduler does not silently retry unknown delivery outcomes. It can
still retry known connection failures. A saved receipt or sent record always
takes precedence over the manual retry option.

A stable Message-ID is retained across retries for correlation with provider
logs. It is not a guarantee that a recipient server will deduplicate messages.
If a provider rejects any recipient, the client message body is not transmitted
to the remaining recipients; this avoids partial delivery followed by duplicate
fallback sends to the accepted addresses.

For the interrupted-delivery patch, deploy these files together:

- `include/intimation_dispatch_helper.php`
- `include/intimation_mailer_factory.php` (new)
- `include/intimation_smtp.php` (new)
- `sendIntimationLetter.php`
- `intimationLetter.php`

There is no schema migration or blanket reset of pending/sent rows. Refresh the
page after deploying so the recovery dialog uses the updated JavaScript.

Inspect `intimation_mail_dispatch.last_error`,
`test_logs/cron_intimation_due_mail_log.txt`, the PHP error log and
`test_logs/intimation_dispatch_state.json`. The page's Last Dispatch Info reads
the structured state, including interrupted and retry-pending runs.

## Local validation

`tests/intimation_dispatch_test.php` is a local-only regression harness (the
repository ignores test files). It creates and drops a uniquely named test
database, uses real TCPDF on PHP 8.5, and replaces only mail transport with a
fake. It does not load the application's database bootstrap or contact SMTP.

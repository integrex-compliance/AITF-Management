<?php
require_once __DIR__ . '/intimation_queue_helper.php';
require_once __DIR__ . '/intimation_mailer_factory.php';

function aitfIntimationInterruptedResult($actionId)
{
    return array('success' => false, 'retry_interrupted_available' => true,
        'action_id' => (int) $actionId,
        'message' => 'The previous attempt ended without a delivery confirmation. You can retry this letter. If the client already received it, retrying may send another copy.');
}

function aitfIsClientStatusLetterApplicableForAutoSend($db, $serviceId, $customerId, $dueDate)
{
    $nextAuditDate = aitfFindNextCertificationServiceAuditDate($db, $serviceId, $customerId);
    return $nextAuditDate === '' || $nextAuditDate > aitfNormalizeDateForComparison($dueDate);
}

function aitfFetchAutoIntimationCandidates($db, $limit = 100, $backlogDays = null)
{
    aitfEnsureIntimationDispatchSchema($db);
    $rows = aitfFetchPendingIntimationRows($db, $backlogDays);
    $receipts = array();
    foreach ($db->query('SELECT delivery_key, attempted_at, state FROM intimation_mail_dispatch')->fetchAll(PDO::FETCH_ASSOC) as $receipt) {
        $receipts[$receipt['delivery_key']] = $receipt;
    }
    // New/unattempted rows first: one bad address must never starve the queue.
    usort($rows, function ($a, $b) use ($receipts) {
        $aReceipt = $receipts[aitfIntimationDeliveryKey($a)] ?? array();
        $bReceipt = $receipts[aitfIntimationDeliveryKey($b)] ?? array();
        $reviewOrder = in_array($aReceipt['state'] ?? '', array('sending', 'transmitting'), true)
            <=> in_array($bReceipt['state'] ?? '', array('sending', 'transmitting'), true);
        return $reviewOrder ?: strcmp((string) ($aReceipt['attempted_at'] ?? ''), (string) ($bReceipt['attempted_at'] ?? ''))
            ?: strcmp($a['due_date'], $b['due_date']) ?: ($a['action_id'] <=> $b['action_id']);
    });
    return array_slice($rows, 0, max(1, min(1000, (int) $limit)));
}

function aitfSendIntimationLetterByActionId($db, $actionId, array $options = array())
{
    aitfEnsureIntimationDispatchSchema($db);
    $stmt = $db->prepare('SELECT csd.*, s.customer_id FROM ca_service_dates csd
        INNER JOIN service s ON s.service_id = csd.service_id WHERE csd.action_id = ?');
    $stmt->execute(array($actionId));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { return array('success' => false, 'message' => 'Letter action not found.'); }
    $key = aitfIntimationDeliveryKey($row);
    // Serialize all letters for this service, including status transitions.
    $lockName = 'intimation:service:' . (int) $row['service_id'];
    $lock = $db->prepare('SELECT GET_LOCK(?, 0)');
    $lock->execute(array($lockName));
    if ((int) $lock->fetchColumn() !== 1) {
        return array('success' => false, 'skipped' => true, 'message' => 'This letter is already being processed. Refresh shortly.');
    }
    $path = '';
    $clientAccepted = false;
    $receiptSaved = false;
    $deliveryPhase = 'preparing';
    $acceptedAt = null;
    try {
        // Re-read under the same lock used by manual, cron and browser dispatch.
        $stmt->execute(array($actionId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['status'] === 'sent') {
            return array('success' => true, 'skipped' => true, 'message' => 'This letter has already been sent.');
        }
        $duplicate = $db->prepare("SELECT sent_date FROM ca_service_dates
            WHERE service_id = ? AND due_date = ? AND status = 'sent'
            AND REPLACE(action, 'Withdrawal Letter', 'Withdraw Letter') = ? LIMIT 1");
        $duplicate->execute(array($row['service_id'], $row['due_date'],
            $row['action'] === 'Withdrawal Letter' ? 'Withdraw Letter' : $row['action']));
        $alreadySentDate = $duplicate->fetchColumn();
        if ($alreadySentDate) {
            $db->prepare("UPDATE ca_service_dates SET status = 'sent', sent_date = ? WHERE action_id = ?")
                ->execute(array($alreadySentDate, $actionId));
            return array('success' => true, 'skipped' => true, 'message' => 'This letter has already been sent.');
        }
        $eligible = $db->prepare('SELECT csd.action_id FROM ca_service_dates csd
            INNER JOIN service s ON s.service_id = csd.service_id
            INNER JOIN customer_info ci ON ci.id = s.customer_id
            WHERE csd.action_id = ? AND ' . aitfIntimationEligibilitySql($options['backlog_days'] ?? null));
        $eligible->execute(array($actionId));
        if (!$eligible->fetchColumn() || !aitfIsClientStatusLetterApplicableForAutoSend($db, $row['service_id'], $row['customer_id'], $row['due_date'])) {
            return array('success' => false, 'skipped' => true, 'message' => 'This letter is no longer due for the current certification cycle. Refresh the page.');
        }
        $receipt = $db->prepare('SELECT * FROM intimation_mail_dispatch WHERE delivery_key = ?');
        $receipt->execute(array($key));
        $delivery = $receipt->fetch(PDO::FETCH_ASSOC);
        if ($delivery && $delivery['accepted_at']) {
            aitfRecordIntimationSent($db, $row, $delivery['accepted_at']);
            return array('success' => true, 'skipped' => true, 'message' => 'Mail was already sent; client status has been synchronized.');
        }
        // The service lock proves no competing sender is still working. New
        // 'connecting' attempts can retry safely; legacy 'sending' and DATA-stage
        // interruptions need a deliberate manual retry because delivery is unknown.
        if ($delivery && in_array($delivery['state'], array('sending', 'transmitting'), true)) {
            if (empty($options['retry_interrupted'])) {
                return aitfIntimationInterruptedResult($actionId);
            }
            error_log('INTIMATION manual recovery authorized for action ' . (int) $actionId);
        }
        $db->prepare("INSERT INTO intimation_mail_dispatch (delivery_key, action_id, state, attempts, attempted_at)
            VALUES (?, ?, 'preparing', 1, NOW()) ON DUPLICATE KEY UPDATE
            state = 'preparing', attempts = attempts + 1, attempted_at = NOW(), last_error = NULL")
            ->execute(array($key, $actionId));
        $cust = $db->prepare('SELECT * FROM customer_info WHERE id = ?');
        $cust->execute(array($row['customer_id']));
        $customer = $cust->fetch(PDO::FETCH_ASSOC);
        aitfLoadPHPMailer();
        $mail = aitfCreateIntimationMailer(function ($phase) use ($db, $key, &$deliveryPhase, &$clientAccepted, &$receiptSaved, &$acceptedAt) {
            $deliveryPhase = $phase;
            if ($phase === 'accepted') {
                $clientAccepted = true;
                $acceptedAt = date('Y-m-d H:i:s');
                $db->prepare("UPDATE intimation_mail_dispatch SET state = 'accepted', accepted_at = ?, last_error = NULL WHERE delivery_key = ?")
                    ->execute(array($acceptedAt, $key));
                $receiptSaved = true;
            } else {
                $db->prepare('UPDATE intimation_mail_dispatch SET state = ? WHERE delivery_key = ?')->execute(array($phase, $key));
            }
        });
        $mail->MessageID = '<intimation-' . $key . '@aitf.co.in>';
        $addresses = array_unique(array_filter(array_map('trim', preg_split('/[,;]+/', (string) $customer['email_id']))));
        if (!$addresses) { throw new RuntimeException('Client email address is missing. Update the client email and retry.'); }
        foreach ($addresses as $address) {
            if (!filter_var($address, FILTER_VALIDATE_EMAIL) || !$mail->addAddress($address)) {
                throw new RuntimeException('Client email address is invalid. Update the client email and retry.');
            }
        }
        $context = aitfIntimationBuildLetterContext($customer, $row);
        $html = aitfIntimationRenderLetterHtml($context);
        $fileName = $context['letter_type'] . '.pdf';
        $dir = __DIR__ . '/../notification';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create the letter attachment directory.');
        }
        $path = tempnam($dir, 'due_');
        if ($path === false) { throw new RuntimeException('Cannot create the letter attachment.'); }
        aitfIntimationBuildPdf($html, $context, $path);
        if (!is_file($path) || filesize($path) < 100 || !$mail->addAttachment($path, $fileName)) {
            throw new RuntimeException('Letter PDF could not be attached.');
        }
        $mail->isHTML(true);
        $mail->Subject = $context['service_desc'] . ' - ' . $customer['customer_name'];
        $mail->Body = aitfIntimationMailBody($customer, $context, $fileName);
        $deliveryPhase = 'connecting';
        $db->prepare("UPDATE intimation_mail_dispatch SET state = 'connecting' WHERE delivery_key = ?")->execute(array($key));
        try {
            list($sent, $error) = aitfSendInvoiceMailer($mail);
        } catch (Throwable $e) {
            if (!$clientAccepted || !$receiptSaved) { throw $e; }
            // SMTP acknowledged DATA; a later QUIT/cleanup error isn't a failed delivery.
            $sent = true;
            $error = '';
            error_log('INTIMATION post-acceptance cleanup: ' . $e->getMessage());
        }
        if (!$sent) { throw new RuntimeException('Mail transport failed: ' . $error); }
        $clientAccepted = true;
        $acceptedAt = $acceptedAt ?: date('Y-m-d H:i:s');
        $db->prepare("UPDATE intimation_mail_dispatch SET state = 'accepted', accepted_at = ?, last_error = NULL WHERE delivery_key = ?")
            ->execute(array($acceptedAt, $key));
        $receiptSaved = true;
        aitfRecordIntimationSent($db, $row, $acceptedAt);

        // Office-copy failure must never undo a successful client send/status.
        $officeSent = false;
        try {
            $office = new PHPMailer;
            $config = aitfGetInvoiceMailConfig();
            aitfAddEmailRecipients($office, implode(',', array_filter(array(AITF_INTIMATION_OFFICE_COPY, $config['admin_email'] ?? ''))));
            $office->isHTML(true);
            $office->Subject = 'Letter sent: ' . $context['display_name'] . ' - ' . $customer['customer_name'];
            $office->Body = $mail->Body;
            $office->addAttachment($path, $fileName);
            list($officeSent, $officeError) = aitfSendInvoiceMailer($office);
            if (!$officeSent) { error_log('INTIMATION OFFICE COPY: ' . $officeError); }
        } catch (Throwable $e) { error_log('INTIMATION OFFICE COPY: ' . $e->getMessage()); }
        return array('success' => true, 'message' => 'Letter sent successfully. Client status updated.', 'office_copy_sent' => $officeSent);
    } catch (Throwable $e) {
        error_log('INTIMATION action ' . (int) $actionId . ': ' . $e->getMessage());
        if (!$clientAccepted && $deliveryPhase !== 'transmitting') {
            $db->prepare("UPDATE intimation_mail_dispatch SET state = 'failed', last_error = ? WHERE delivery_key = ?")
                ->execute(array($e->getMessage(), $key));
        }
        if (!$clientAccepted && $deliveryPhase === 'transmitting') {
            $db->prepare('UPDATE intimation_mail_dispatch SET last_error = ? WHERE delivery_key = ?')->execute(array($e->getMessage(), $key));
            return aitfIntimationInterruptedResult($actionId);
        }
        return array('success' => false, 'message' => $clientAccepted
            ? ($receiptSaved ? 'Mail was accepted, but status synchronization failed. Refresh and retry to synchronize; the saved receipt prevents resending.'
                : 'Mail was accepted, but its receipt could not be saved. Check the SMTP log before retrying this letter.')
            : $e->getMessage());
    } finally {
        if ($path && is_file($path)) { unlink($path); }
        try { $db->prepare('SELECT RELEASE_LOCK(?)')->execute(array($lockName)); }
        catch (Throwable $e) { error_log('INTIMATION lock cleanup: ' . $e->getMessage()); }
    }
}

function aitfRunAutoIntimationLetters($db, $limit = 100, $dryRun = false, $backlogDays = null)
{
    $rows = $dryRun ? array_slice(aitfFetchPendingIntimationRows($db, $backlogDays), 0, max(1, min(1000, (int) $limit)))
        : aitfFetchAutoIntimationCandidates($db, $limit, $backlogDays);
    $result = array('checked' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'remaining' => false, 'items' => array());
    $started = microtime(true);
    foreach ($rows as $row) {
        if (!$dryRun && microtime(true) - $started >= 240) { $result['remaining'] = true; break; }
        $result['checked']++;
        try {
            $item = $dryRun ? array('success' => true, 'skipped' => true, 'message' => 'Dry run; no email sent.')
                : aitfSendIntimationLetterByActionId($db, $row['action_id'], array('backlog_days' => $backlogDays));
        } catch (Throwable $e) { $item = array('success' => false, 'message' => $e->getMessage()); }
        if (!empty($item['skipped'])) { $result['skipped']++; }
        elseif (!empty($item['success'])) { $result['sent']++; }
        else { $result['failed']++; }
        $result['items'][] = array_merge(array('action_id' => $row['action_id'], 'letter' => $row['action'], 'due_date' => $row['due_date']), $item);
    }
    $result['remaining'] = $result['remaining'] || count($rows) >= max(1, min(1000, (int) $limit));
    return $result;
}

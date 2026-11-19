<?php
require_once __DIR__ . '/env_helper.php';
require_once __DIR__ . '/certification_cycle_helper.php';

function aitfIntimationBacklogDays($days = null)
{
    $days = $days === null ? aitf_env('AUTO_INTIMATION_BACKLOG_DAYS', 30) : $days;
    // Never allow an unlimited historical backlog, including via URL parameters.
    return is_numeric($days) && (int) $days >= 0 ? min(90, (int) $days) : 30;
}

function aitfIntimationEligibilitySql($backlogDays = null)
{
    $days = aitfIntimationBacklogDays($backlogDays);
    $latest = aitfLatestCertificationServiceSubquery('ci');
    return "csd.is_deleted = 'N'
        AND (csd.is_intimation_deleted IS NULL OR csd.is_intimation_deleted = 'N')
        AND (ci.is_deleted IS NULL OR ci.is_deleted <> 'YES')
        AND (ci.audit_status IS NULL OR ci.audit_status NOT IN ('Withdrawn', 'Application canceled'))
        AND s.service_id = $latest
        AND csd.action IN ('Suspension Reminder','Suspend Letter','Withdrawal Reminder',
                          'Withdraw Letter','Withdrawal Letter','1st Reminder','2nd Reminder','Expire Letter')
        AND csd.due_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
        AND csd.due_date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        AND (csd.status IS NULL OR csd.status <> 'sent')
        AND NOT EXISTS (SELECT 1 FROM ca_service_dates later_letter
            WHERE later_letter.service_id = csd.service_id
            AND later_letter.due_date > csd.due_date
            AND later_letter.status = 'sent' AND later_letter.is_deleted = 'N'
            AND later_letter.action IN ('Suspension Reminder','Suspend Letter','Withdrawal Reminder',
                'Withdraw Letter','Withdrawal Letter','1st Reminder','2nd Reminder','Expire Letter'))";
}

function aitfEnsureIntimationDispatchSchema($db)
{
    static $ready = array();
    $connection = spl_object_id($db);
    if (isset($ready[$connection])) { return; }
    $db->exec("CREATE TABLE IF NOT EXISTS intimation_mail_dispatch (
        delivery_key CHAR(64) NOT NULL PRIMARY KEY,
        action_id INT NOT NULL,
        state VARCHAR(20) NOT NULL DEFAULT 'pending',
        attempts INT NOT NULL DEFAULT 0,
        attempted_at DATETIME NULL,
        accepted_at DATETIME NULL,
        last_error TEXT NULL,
        INDEX idx_intimation_dispatch_action (action_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ready[$connection] = true;
}

function aitfIntimationDeliveryKey(array $row)
{
    $action = $row['action'] === 'Withdrawal Letter' ? 'Withdraw Letter' : $row['action'];
    return hash('sha256', $row['service_id'] . '|' . $action . '|' . substr($row['due_date'], 0, 10));
}

function aitfFetchPendingIntimationRows($db, $backlogDays = null)
{
    $where = aitfIntimationEligibilitySql($backlogDays);
    $stmt = $db->query("SELECT csd.*, s.customer_id, s.service_type,
        ci.id, ci.customer_name, ci.unique_id, ci.email_id
        FROM ca_service_dates csd
        INNER JOIN service s ON s.service_id = csd.service_id
        INNER JOIN customer_info ci ON ci.id = s.customer_id
        WHERE $where ORDER BY csd.due_date, csd.action_id");
    $rows = array();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = aitfIntimationDeliveryKey($row);
        if (!isset($rows[$key])) { $rows[$key] = $row; }
    }
    return array_values($rows);
}

function aitfIntimationStatusForAction($action)
{
    $statuses = array('Suspend Letter' => 'Suspended', 'Withdraw Letter' => 'Withdrawn',
        'Withdrawal Letter' => 'Withdrawn', 'Expire Letter' => 'Expired');
    return isset($statuses[$action]) ? $statuses[$action] : null;
}

function aitfRecordIntimationSent($db, array $row, $acceptedAt)
{
    // The durable SMTP receipt is saved before this transaction. A later retry can
    // repair these updates without sending the client another copy.
    $db->beginTransaction();
    try {
        $actions = in_array($row['action'], array('Withdraw Letter', 'Withdrawal Letter'), true)
            ? array('Withdraw Letter', 'Withdrawal Letter') : array($row['action'], $row['action']);
        $stmt = $db->prepare("UPDATE ca_service_dates SET status = 'sent', sent_date = ?
            WHERE service_id = ? AND due_date = ? AND action IN (?, ?) AND is_deleted = 'N'");
        $stmt->execute(array(substr($acceptedAt, 0, 10), $row['service_id'], $row['due_date'], $actions[0], $actions[1]));
        $status = aitfIntimationStatusForAction($row['action']);
        if ($status !== null) {
            $stmt = $db->prepare('UPDATE customer_info SET audit_status = ? WHERE id = ?');
            $stmt->execute(array($status, $row['customer_id']));
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        throw $e;
    }
}

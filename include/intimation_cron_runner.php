<?php
// Both legacy scheduler URLs use this entry point. Run every five minutes;
// dispatch starts at noon Asia/Kolkata and retries pending letters afterwards.
ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/env_helper.php';
$isCli = PHP_SAPI === 'cli';
$dryRun = $isCli ? in_array('--dry-run', $argv, true) : (($_GET['dry_run'] ?? '') === '1');
$limit = 100;
$backlogDays = null;
$runLock = false;
$result = array('success' => false);
$logDir = dirname(__DIR__) . '/test_logs';
$statePath = $logDir . '/intimation_dispatch_state.json';
try {
    if (!$isCli) {
        require_once __DIR__ . '/session_helper.php';
        aitf_start_session();
        $token = (string) aitf_env('AUTO_INTIMATION_TOKEN', '');
        $tokenValid = $token !== '' && hash_equals($token, (string) ($_GET['token'] ?? ''));
        $staff = !empty($_SESSION['username']) && !empty($_SESSION['role']) &&
            !in_array(strtolower($_SESSION['role']), array('client', 'external', 'auditor'), true);
        session_write_close();
        // Existing hosting URL jobs use this legacy entry point without a
        // session. Preserve them until an AUTO_INTIMATION_TOKEN is configured.
        $legacyJob = defined('AITF_LEGACY_INTIMATION_CRON') && AITF_LEGACY_INTIMATION_CRON && $token === '';
        $localJob = $token === '' && in_array($_SERVER['REMOTE_ADDR'] ?? '', array('127.0.0.1', '::1'), true);
        if (!$staff && !$tokenValid && !$legacyJob && !$localJob) {
            http_response_code(403);
            throw new RuntimeException('An authorized staff session or AUTO_INTIMATION_TOKEN is required.');
        }
    } else {
        foreach ($argv as $arg) {
            if (strpos($arg, '--limit=') === 0) { $limit = (int) substr($arg, 8); }
            if (strpos($arg, '--backlog-days=') === 0) { $backlogDays = (int) substr($arg, 15); }
        }
    }
    ignore_user_abort(true);
    set_time_limit(0);
    require_once dirname(__DIR__) . '/dbConnect.php';
    if (session_status() === PHP_SESSION_ACTIVE) { session_write_close(); }
    if (!$db) { throw new RuntimeException('Database connection unavailable.'); }
    require_once __DIR__ . '/intimation_letter_auto_helper.php';
    $lock = $db->query("SELECT GET_LOCK('aitf_intimation_dispatch_run', 0)");
    $runLock = (int) $lock->fetchColumn() === 1;
    if (!$runLock) {
        $result = array('success' => true, 'skipped' => true, 'message' => 'A dispatch is already running.');
    } elseif (!$dryRun && (int) date('H') < 12) {
        $result = array('success' => true, 'skipped' => true, 'message' => 'Automatic dispatch starts at 12:00 PM Asia/Kolkata.');
    } else {
        $state = is_file($statePath) ? json_decode(file_get_contents($statePath), true) : array();
        if (!$dryRun && !empty($state['attempted_at']) && time() - (int) $state['attempted_at'] < 300) {
            $result = array('success' => true, 'skipped' => true, 'message' => 'Next automatic retry is due within five minutes.');
        } else {
            if (!is_dir($logDir) && !mkdir($logDir, 0775, true) && !is_dir($logDir)) {
                throw new RuntimeException('Cannot create dispatch log directory.');
            }
            $startedAt = time();
            if (!$dryRun) {
                $state = array('attempted_at' => $startedAt, 'date' => date('Y-m-d H:i:s'), 'status' => 'Running', 'total' => 0, 'success' => 0, 'failed' => 0);
                if (file_put_contents($statePath, json_encode($state), LOCK_EX) === false) {
                    throw new RuntimeException('Cannot write dispatch state.');
                }
            }
            $result = aitfRunAutoIntimationLetters($db, $limit, $dryRun, $backlogDays);
            $result['success'] = $result['failed'] === 0;
            $result['dry_run'] = $dryRun;
            $result['run_at'] = date('Y-m-d H:i:s');
            if (!$dryRun) {
                foreach ($result['items'] as $item) {
                    file_put_contents($logDir . '/cron_intimation_due_mail_log.txt', '[' . date('Y-m-d H:i:s') . '] action ' . $item['action_id'] . ': ' . json_encode($item, JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                $status = $result['failed'] ? 'Retry pending' : ($result['remaining'] ? 'More pending; next batch in five minutes' : 'Completed');
                $state = array('attempted_at' => $startedAt, 'date' => $result['run_at'], 'status' => $status,
                    'total' => $result['checked'], 'success' => $result['sent'], 'failed' => $result['failed']);
                file_put_contents($statePath, json_encode($state), LOCK_EX);
                file_put_contents($logDir . '/cron_intimation_due_mail_log.txt', '[' . date('Y-m-d H:i:s') . '] === AUTOMATED CRON JOB COMPLETED: ' . $result['sent'] . ' succeeded, ' . $result['failed'] . ' failed ===' . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
    }
} catch (Throwable $e) {
    error_log('INTIMATION CRON: ' . $e->getMessage());
    $result = array('success' => false, 'message' => $e->getMessage());
    if (!$isCli && http_response_code() !== 403) { http_response_code(500); }
    if (!$dryRun && $runLock && isset($startedAt)) {
        file_put_contents($statePath, json_encode(array('attempted_at' => $startedAt,
            'date' => date('Y-m-d H:i:s'), 'status' => 'Failed; retry pending', 'total' => 0, 'success' => 0, 'failed' => 1)), LOCK_EX);
    }
} finally {
    if ($runLock) {
        try { $db->query("SELECT RELEASE_LOCK('aitf_intimation_dispatch_run')"); }
        catch (Throwable $e) { error_log('INTIMATION CRON lock cleanup: ' . $e->getMessage()); }
    }
}
ob_end_clean();
if (!$isCli) { header('Content-Type: application/json; charset=utf-8'); }
echo json_encode($result, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL;
if ($isCli && empty($result['success'])) { exit(1); }

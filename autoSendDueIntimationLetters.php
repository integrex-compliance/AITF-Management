<?php

require_once __DIR__ . '/include/intimation_letter_auto_helper.php';

date_default_timezone_set('Asia/Kolkata');

$isCli = (PHP_SAPI === 'cli');
$limit = 25;
$dryRun = false;
$backlogDays = null;

if ($isCli) {
    foreach ($argv as $arg) {
        if ($arg === '--dry-run') {
            $dryRun = true;
        } elseif (strpos($arg, '--limit=') === 0) {
            $limit = (int) substr($arg, 8);
        } elseif (strpos($arg, '--backlog-days=') === 0) {
            $backlogDays = (int) substr($arg, 15);
        }
    }
} else {
    $configuredToken = aitf_env('AUTO_INTIMATION_TOKEN', '');
    $requestToken = isset($_GET['token']) ? (string) $_GET['token'] : '';
    $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $isLocalRequest = in_array($remoteAddr, array('', '127.0.0.1', '::1'), true);

    if ($configuredToken !== '') {
        if (!hash_equals($configuredToken, $requestToken)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Invalid token.'));
            exit;
        }
    } elseif (!$isLocalRequest) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'AUTO_INTIMATION_TOKEN is required for non-local web runs.'));
        exit;
    }

    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : $limit;
    $dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
    $backlogDays = isset($_GET['backlog_days']) ? (int) $_GET['backlog_days'] : null;
}

try {
    $result = aitfRunAutoIntimationLetters($db, $limit, $dryRun, $backlogDays);
    $result['success'] = ($result['failed'] === 0);
    $result['dry_run'] = $dryRun;
    $result['backlog_days'] = $backlogDays;
    $result['run_at'] = date('Y-m-d H:i:s');

    if ($isCli) {
        echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        header('Content-Type: application/json');
        echo json_encode($result);
    }
} catch (Exception $ex) {
    if ($isCli) {
        fwrite(STDERR, $ex->getMessage() . PHP_EOL);
        exit(1);
    }

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => $ex->getMessage()));
}

<?php
$reason = ($_GET['reason'] ?? '') === 'not_found' ? 'not_found' : 'login_required';
$documentRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
$applicationRoot = realpath(__DIR__);
$basePath = '';

if (
    $documentRoot !== false
    && $applicationRoot !== false
    && strpos(strtolower($applicationRoot), strtolower($documentRoot)) === 0
) {
    $relativePath = substr($applicationRoot, strlen($documentRoot));
    $relativePath = trim(str_replace('\\', '/', (string)$relativePath), '/');
    $basePath = $relativePath !== '' ? '/' . $relativePath : '';
}

header('Location: ' . $basePath . '/login.php?reason=' . urlencode($reason));
exit;


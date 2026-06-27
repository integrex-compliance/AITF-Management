<?php

header('Content-Type: application/json');

if (!isset($db)) {
    include_once './dbConnect.php';
}
require_once __DIR__ . '/include/iaf_dashboard_data.php';

if (!isset($db) || !$db instanceof PDO) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.',
        'rows_html' => '',
        'next_offset' => 0,
        'has_more' => false,
    ]);
    exit;
}

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';

try {
    $result = iafDashboardFetchRows($db, $offset, $limit, $search);
    $rowsHtml = '';
    foreach ($result['rows'] as $row) {
        $rowsHtml .= iafDashboardRenderRow($row);
    }

    echo json_encode([
        'status' => 'success',
        'rows_html' => $rowsHtml,
        'next_offset' => $result['next_offset'],
        'has_more' => $result['has_more'],
    ]);
} catch (Exception $e) {
    error_log('IAF dashboard data load failed: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Dashboard data load failed.',
        'rows_html' => '',
        'next_offset' => $offset,
        'has_more' => false,
    ]);
}

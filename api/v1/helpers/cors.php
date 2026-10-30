<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\cors.php

// Handle preflight OPTIONS and CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

// Return a controlled response instead of exposing database paths or SQL details.
set_exception_handler(function (Throwable $error) {
    error_log('API error: ' . get_class($error) . ' code=' . $error->getCode());
    jsonResponse(false, 'The server could not complete this request. Please try again later.', [], 503);
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function jsonResponse($success, $message, $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json; charset=UTF-8");
    $response = [
        'success' => (bool)$success,
        'message' => $message,
    ];
    if (!empty($data) || is_array($data)) {
        if (isset($data['records']) || isset($data['tasks']) || isset($data['data'])) {
            $response = array_merge($response, $data);
        } else {
            $response['data'] = $data;
        }
    }
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || substr(ltrim($raw), 0, 1) !== '{') {
        jsonResponse(false, 'A valid JSON object is required.', [], 400);
    }
    return $decoded;
}

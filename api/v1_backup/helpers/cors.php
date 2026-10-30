<?php
// C:\xampp\htdocs\public_html\api\v1\helpers\cors.php

// Handle preflight OPTIONS and CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

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
    return is_array($decoded) ? $decoded : $_POST;
}

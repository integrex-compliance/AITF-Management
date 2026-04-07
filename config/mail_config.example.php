<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    http_response_code(403);
    exit;
}

return [
    'host' => 'smtpout.secureserver.net',
    'port' => 587,
    'secure' => 'tls',
    'username' => 'certification@aitf.co.in',
    'password' => 'AITF999@@HasTag#'
];

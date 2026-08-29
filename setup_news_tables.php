<?php
include_once __DIR__ . '/dbConnect.php';

if (!$db) {
    echo "Database connection failed\n";
    exit(1);
}

require_once __DIR__ . '/include/news_helper.php';

try {
    aitf_ensure_news_schema($db);
    echo "News tables created cleanly without dummy data.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

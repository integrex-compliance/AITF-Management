<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$svc_id1 = '300608';
$customerId = '1237';
$jsonFile = __DIR__ . '/uploads/reviews/data_' . $svc_id1 . '_' . $customerId . '.json';

if (file_exists($jsonFile)) {
    echo "File exists!\n";
    $jsonData = json_decode(file_get_contents($jsonFile), true);
    if ($jsonData && isset($jsonData['review_date'])) {
        $reviewDateStr = $jsonData['review_date'];
        echo "reviewDateStr: '$reviewDateStr'\n";
        
        $dateObj = DateTime::createFromFormat('d.m.Y', $reviewDateStr);
        if ($dateObj) {
            $reviewDateFormatted = $dateObj->format('Y-m-d');
            echo "Formatted review date: '$reviewDateFormatted'\n";
            
            $date = date("Y-m-d", strtotime("03.08.2026"));
            echo "Intimation date: '$date'\n";
            
            if ($reviewDateFormatted > $date) {
                echo "ERROR WOULD TRIGGER: " . $dateObj->format('d.m.Y') . "\n";
            } else {
                echo "No error.\n";
            }
        } else {
            echo "Failed to parse date string.\n";
        }
    } else {
        echo "review_date not set in json.\n";
    }
} else {
    echo "File does not exist: $jsonFile\n";
}
?>

<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $serviceId = $_GET['serviceId'] ?? '';
    $customerId = $_GET['customerId'] ?? '';
    
    try {
        // Read from JSON file (temporary solution matching saveInfoUpdationReview.php)
        $filename = 'uploads/reviews/data_' . $serviceId . '_' . $customerId . '.json';
        
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            echo json_encode($data);
        } else {
            echo json_encode(null);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

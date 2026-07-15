<?php
// Clean start - no extra output
ob_start();

include_once './dbConnect.php';

// Clear any previous output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    if (isset($_GET['assessmentId']) && !empty($_GET['assessmentId'])) {
        $statement = $db->prepare("SELECT * FROM assessment_planning WHERE assessment_id = ?");
        $statement->execute([$_GET['assessmentId']]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        echo json_encode(['error' => 'No ID']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error']);
}

exit;

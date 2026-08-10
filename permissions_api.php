<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once 'dbConnect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Main Admin') {
    require_once __DIR__ . '/utility.php';
    if (!Utitlity::checkForDirector()) {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit;
    }
}

global $db;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_users') {
    $stmt = $db->query("SELECT user_id as id, user_name as username, role, e_id FROM user WHERE role != 'client' ORDER BY user_name ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "users" => $users]);
    exit;
}

if ($action === 'get_permissions') {
    $user_id = intval($_GET['user_id'] ?? 0);
    $stmt = $db->prepare("SELECT feature, is_allowed FROM user_permissions WHERE user_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Default structure
    $perms = [
        'client_access' => false,
        'invoice_access' => false
    ];
    
    foreach ($permissions as $p) {
        if ($p['feature'] == 'client_access') $perms['client_access'] = (bool)$p['is_allowed'];
        if ($p['feature'] == 'invoice_access') $perms['invoice_access'] = (bool)$p['is_allowed'];
    }
    
    echo json_encode(["status" => "success", "permissions" => $perms]);
    exit;
}

if ($action === 'update_permission') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $feature = $_POST['feature'] ?? '';
    $is_allowed = intval($_POST['is_allowed'] ?? 0);
    
    if (!$user_id || !in_array($feature, ['client_access', 'invoice_access'])) {
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO user_permissions (user_id, feature, is_allowed) VALUES (:uid, :feat, :allow) 
                          ON DUPLICATE KEY UPDATE is_allowed = :allow2");
    $stmt->execute([
        'uid' => $user_id,
        'feat' => $feature,
        'allow' => $is_allowed,
        'allow2' => $is_allowed
    ]);
    
    echo json_encode(["status" => "success", "message" => "Permission updated"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Unknown action"]);
exit;
?>

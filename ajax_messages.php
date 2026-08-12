<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/message_helper.php';

aitf_ensure_message_schema($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$myId = $_SESSION['id'] ?? null;
$role = $_SESSION['role'] ?? '';

if ($myId === null && $role !== 'Director') {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// If Director is logged in (hardcoded login sets id to 0), resolve their actual user_id from DB
if ($role === 'Director' && empty($myId)) {
    $stmt = $db->prepare("SELECT user_id FROM user WHERE role = 'Director' AND is_deleted = '0' ORDER BY user_id ASC LIMIT 1");
    $stmt->execute();
    $dirRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($dirRow) {
        $myId = (int)$dirRow['user_id'];
    } else {
        // Fallback if no director in DB, just use 0 (but it might cause mismatch)
        $myId = 0;
    }
}

if ($action === 'fetch_conversation') {
    $otherUserId = (int)$_POST['other_user_id'];
    
    // Mark messages from other user as read
    $updateStmt = $db->prepare("UPDATE user_messages SET is_read = 1 WHERE sender_id = :other_id AND receiver_id = :my_id AND is_read = 0");
    $updateStmt->execute([':other_id' => $otherUserId, ':my_id' => $myId]);

    // Fetch conversation (LEFT JOIN to handle sender_id = 0 which is Director not in DB)
    $stmt = $db->prepare("SELECT m.*, COALESCE(u.user_name, IF(m.sender_id=0, 'Managing Director', 'Unknown')) as sender_name 
                          FROM user_messages m 
                          LEFT JOIN user u ON m.sender_id = u.user_id 
                          WHERE (m.sender_id = :my_id AND m.receiver_id = :other_id) 
                             OR (m.sender_id = :other_id AND m.receiver_id = :my_id) 
                          ORDER BY m.created_at ASC");
    $stmt->execute([':my_id' => $myId, ':other_id' => $otherUserId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages, 'my_id' => $myId]);
    exit;
}

if ($action === 'send_message') {
    $receiverId = (int)$_POST['receiver_id'];
    $messageText = trim((string)$_POST['message']);
    $attachmentPath = null;
    
    if (empty($messageText) && empty($_FILES['attachment']['name'])) {
        echo json_encode(['success' => false, 'message' => 'Message or attachment is required']);
        exit;
    }

    // Handle file upload
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = 'uploads/messages/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $_FILES['attachment']['name']);
        $destPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $destPath)) {
            $attachmentPath = $destPath;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload attachment']);
            exit;
        }
    }

    $stmt = $db->prepare("INSERT INTO user_messages (sender_id, receiver_id, message, attachment) VALUES (:sender, :receiver, :msg, :att)");
    if ($stmt->execute([
        ':sender' => $myId,
        ':receiver' => $receiverId,
        ':msg' => $messageText,
        ':att' => $attachmentPath
    ])) {
        // Send email notification to receiver if valid
        if ($receiverId !== 0) {
            $recStmt = $db->prepare("SELECT user_name, email_id FROM user WHERE user_id = :id");
            $recStmt->execute([':id' => $receiverId]);
            $receiverInfo = $recStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($receiverInfo && filter_var($receiverInfo['email_id'], FILTER_VALIDATE_EMAIL)) {
                aitf_send_message_notification($receiverInfo['email_id'], $receiverInfo['user_name']);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

if ($action === 'get_director_id') {
    $stmt = $db->prepare("SELECT user_id, user_name FROM user WHERE role = 'Director' AND is_deleted = '0' ORDER BY user_id ASC LIMIT 1");
    $stmt->execute();
    $director = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($director) {
        echo json_encode(['success' => true, 'director_id' => $director['user_id'], 'director_name' => $director['user_name']]);
    } else {
        // Fallback to 0 for Managing Director if not in user table
        echo json_encode(['success' => true, 'director_id' => 0, 'director_name' => 'Managing Director']);
    }
    exit;
}

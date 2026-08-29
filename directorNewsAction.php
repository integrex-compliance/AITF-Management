<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

include_once __DIR__ . '/dbConnect.php';
include_once __DIR__ . '/utility.php';
require_once __DIR__ . '/include/rbac_helper.php';
require_once __DIR__ . '/include/news_helper.php';
require_once __DIR__ . '/testemail.php';

date_default_timezone_set('Asia/Kolkata');

function director_news_redirect($message, $type = 'success')
{
    $_SESSION['director_notice'] = $message;
    $_SESSION['director_notice_type'] = $type;
    header('Location: directorSpace.php?tab=news');
    exit;
}

if (!Utitlity::checkForDirector()) {
    $_SESSION['login_notice'] = 'Please log in again to continue.';
    header('Location: login.php?reason=login_required');
    exit;
}

if (!$db) {
    director_news_redirect('Database connection is not available.', 'error');
}

aitf_ensure_news_schema($db);

// Function to broadcast news to all verified subscribers
function broadcastNewsToSubscribers($db, $newsId, $title, $category, $summary, $documentPath, $documentName)
{
    try {
        $subStmt = $db->query("SELECT email FROM news_subscribers WHERE status = 'verified'");
        $subscribers = $subStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($subscribers)) {
            return 0;
        }

        $emailSender = new AITFServiceEmailSender();
        $subject = "AITF Update: " . htmlspecialchars_decode($title, ENT_QUOTES);
        
        $articleUrl = "https://www.aitf.co.in/news_detail.php?id=" . urlencode($newsId);
        $docDownloadHtml = "";
        if (!empty($documentPath)) {
            $docUrl = "https://www.aitf.co.in/" . htmlspecialchars($documentPath);
            $docName = htmlspecialchars($documentName ?: 'Official Document');
            $docDownloadHtml = "<div style='margin-top: 15px; padding: 12px 16px; background: #eff6ff; border-radius: 6px; border-left: 4px solid #3b82f6;'>
                <span style='font-size: 13px; color: #1e40af;'>📎 <strong>Attached Document:</strong> <a href='{$docUrl}' target='_blank' style='color: #2563eb; text-decoration: underline;'>Download {$docName}</a></span>
            </div>";
        }

        $sentCount = 0;
        foreach ($subscribers as $subEmail) {
            $body = "<div style='font-family: Arial, sans-serif; max-width: 650px; margin: auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                <div style='border-bottom: 2px solid #053c6d; padding-bottom: 15px; margin-bottom: 20px; display: flex; align-items: center;'>
                    <div>
                        <h2 style='color: #053c6d; margin: 0; font-size: 22px;'>AITF Management Certification</h2>
                        <span style='background: #053c6d; color: #ffffff; font-size: 11px; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; font-weight: bold; margin-top: 4px; display: inline-block;'>
                            " . htmlspecialchars($category) . "
                        </span>
                    </div>
                </div>

                <h3 style='color: #0f172a; font-size: 20px; line-height: 1.4; margin-top: 0;'>
                    " . htmlspecialchars($title) . "
                </h3>

                <p style='color: #475569; font-size: 15px; line-height: 1.6; margin: 15px 0;'>
                    " . nl2br(htmlspecialchars($summary)) . "
                </p>

                {$docDownloadHtml}

                <div style='text-align: center; margin: 30px 0 20px;'>
                    <a href='{$articleUrl}' style='display: inline-block; background: linear-gradient(135deg, #053c6d, #0b5b94); color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: bold; font-size: 14px;'>
                        Read Full Article &amp; Details →
                    </a>
                </div>

                <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                <p style='font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.5;'>
                    You received this email because you subscribed to AITF official announcements.<br>
                    © " . date('Y') . " AITF Management Certification Limited. All rights reserved.
                </p>
            </div>";

            try {
                $emailSender->sendEmail($subEmail, 'Subscriber', $body, $subject);
                $sentCount++;
            } catch (Exception $e) {
                error_log("Broadcast Error to {$subEmail}: " . $e->getMessage());
            }
        }

        return $sentCount;
    } catch (Exception $e) {
        error_log("Broadcast function error: " . $e->getMessage());
        return 0;
    }
}

$action = trim((string)($_POST['action'] ?? ''));

// 1. ADD / POST NEWS
if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General Announcement');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Director / Management');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $sendBroadcast = isset($_POST['send_broadcast']) ? true : false;

    if (empty($title) || empty($content)) {
        director_news_redirect('Headline and Article Content are required.', 'error');
    }

    // Handle Image Upload
    $imagePath = null;
    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
        $allowedImgExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileInfo = pathinfo($_FILES['news_image']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (in_array($ext, $allowedImgExt, true)) {
            $imgDir = __DIR__ . '/uploads/news_images/';
            if (!is_dir($imgDir)) { @mkdir($imgDir, 0777, true); }
            $newImgName = 'news_img_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['news_image']['tmp_name'], $imgDir . $newImgName)) {
                $imagePath = 'uploads/news_images/' . $newImgName;
            }
        }
    }

    // Handle Document Upload
    $documentPath = null;
    $documentName = null;
    if (isset($_FILES['news_document']) && $_FILES['news_document']['error'] === UPLOAD_ERR_OK) {
        $allowedDocExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
        $origName = basename($_FILES['news_document']['name']);
        $fileInfo = pathinfo($origName);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (in_array($ext, $allowedDocExt, true)) {
            $docDir = __DIR__ . '/uploads/news_docs/';
            if (!is_dir($docDir)) { @mkdir($docDir, 0777, true); }
            $newDocName = 'news_doc_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['news_document']['tmp_name'], $docDir . $newDocName)) {
                $documentPath = 'uploads/news_docs/' . $newDocName;
                $documentName = $origName;
            }
        }
    }

    try {
        $stmt = $db->prepare("INSERT INTO news_articles (title, category, summary, content, image_path, document_path, document_name, author, is_featured, status) 
                              VALUES (:title, :category, :summary, :content, :image_path, :document_path, :document_name, :author, :is_featured, 'published')");
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':summary' => $summary,
            ':content' => $content,
            ':image_path' => $imagePath,
            ':document_path' => $documentPath,
            ':document_name' => $documentName,
            ':author' => $author,
            ':is_featured' => $isFeatured
        ]);
        $newNewsId = $db->lastInsertId();

        $msg = "News article '{$title}' posted successfully.";

        if ($sendBroadcast && $newNewsId) {
            $sentCount = broadcastNewsToSubscribers($db, $newNewsId, $title, $category, $summary ?: mb_substr(strip_tags($content), 0, 160), $documentPath, $documentName);
            $msg .= " Notification sent to {$sentCount} verified subscribers.";
        }

        director_news_redirect($msg, 'success');
    } catch (Exception $e) {
        error_log("Error posting news: " . $e->getMessage());
        director_news_redirect('Failed to post news: ' . $e->getMessage(), 'error');
    }
}

// 2. EDIT NEWS
if ($action === 'edit') {
    $id = (int)($_POST['news_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General Announcement');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Director / Management');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = trim($_POST['status'] ?? 'published');

    if ($id <= 0 || empty($title) || empty($content)) {
        director_news_redirect('Invalid news details.', 'error');
    }

    try {
        $currStmt = $db->prepare("SELECT image_path, document_path, document_name FROM news_articles WHERE id = :id LIMIT 1");
        $currStmt->execute([':id' => $id]);
        $curr = $currStmt->fetch(PDO::FETCH_ASSOC);

        $imagePath = $curr['image_path'] ?? null;
        if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
            $allowedImgExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileInfo = pathinfo($_FILES['news_image']['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (in_array($ext, $allowedImgExt, true)) {
                $imgDir = __DIR__ . '/uploads/news_images/';
                if (!is_dir($imgDir)) { @mkdir($imgDir, 0777, true); }
                $newImgName = 'news_img_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['news_image']['tmp_name'], $imgDir . $newImgName)) {
                    $imagePath = 'uploads/news_images/' . $newImgName;
                }
            }
        }

        $documentPath = $curr['document_path'] ?? null;
        $documentName = $curr['document_name'] ?? null;
        if (isset($_FILES['news_document']) && $_FILES['news_document']['error'] === UPLOAD_ERR_OK) {
            $allowedDocExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
            $origName = basename($_FILES['news_document']['name']);
            $fileInfo = pathinfo($origName);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (in_array($ext, $allowedDocExt, true)) {
                $docDir = __DIR__ . '/uploads/news_docs/';
                if (!is_dir($docDir)) { @mkdir($docDir, 0777, true); }
                $newDocName = 'news_doc_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['news_document']['tmp_name'], $docDir . $newDocName)) {
                    $documentPath = 'uploads/news_docs/' . $newDocName;
                    $documentName = $origName;
                }
            }
        }

        $stmt = $db->prepare("UPDATE news_articles SET title = :title, category = :category, summary = :summary, content = :content, image_path = :image_path, document_path = :document_path, document_name = :document_name, author = :author, is_featured = :is_featured, status = :status WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':summary' => $summary,
            ':content' => $content,
            ':image_path' => $imagePath,
            ':document_path' => $documentPath,
            ':document_name' => $documentName,
            ':author' => $author,
            ':is_featured' => $isFeatured,
            ':status' => $status,
            ':id' => $id
        ]);

        director_news_redirect("News article updated successfully.", 'success');
    } catch (Exception $e) {
        error_log("Error editing news: " . $e->getMessage());
        director_news_redirect('Failed to update news: ' . $e->getMessage(), 'error');
    }
}

// 3. DELETE NEWS
if ($action === 'delete') {
    $id = (int)($_POST['news_id'] ?? 0);
    if ($id <= 0) {
        director_news_redirect('Invalid news ID.', 'error');
    }

    try {
        $stmt = $db->prepare("DELETE FROM news_articles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        director_news_redirect("News article deleted successfully.", 'success');
    } catch (Exception $e) {
        error_log("Error deleting news: " . $e->getMessage());
        director_news_redirect('Failed to delete news.', 'error');
    }
}

// 4. MANUAL BROADCAST NEWS TO SUBSCRIBERS
if ($action === 'broadcast') {
    $id = (int)($_POST['news_id'] ?? 0);
    if ($id <= 0) {
        director_news_redirect('Invalid news ID.', 'error');
    }

    try {
        $stmt = $db->prepare("SELECT * FROM news_articles WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$news) {
            director_news_redirect('News article not found.', 'error');
        }

        $sentCount = broadcastNewsToSubscribers($db, $news['id'], $news['title'], $news['category'], $news['summary'] ?: mb_substr(strip_tags($news['content']), 0, 160), $news['document_path'], $news['document_name']);
        director_news_redirect("Broadcast sent successfully to {$sentCount} verified subscribers.", 'success');
    } catch (Exception $e) {
        error_log("Broadcast action error: " . $e->getMessage());
        director_news_redirect('Broadcast failed: ' . $e->getMessage(), 'error');
    }
}

// 5. DELETE SUBSCRIBER
if ($action === 'delete_subscriber') {
    $subId = (int)($_POST['subscriber_id'] ?? 0);
    if ($subId <= 0) {
        director_news_redirect('Invalid subscriber ID.', 'error');
    }

    try {
        $stmt = $db->prepare("DELETE FROM news_subscribers WHERE id = :id");
        $stmt->execute([':id' => $subId]);
        director_news_redirect("Subscriber removed successfully.", 'success');
    } catch (Exception $e) {
        error_log("Error deleting subscriber: " . $e->getMessage());
        director_news_redirect('Failed to remove subscriber.', 'error');
    }
}

director_news_redirect('Invalid request action.', 'error');

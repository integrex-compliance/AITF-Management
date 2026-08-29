<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/news_helper.php';

$newsId = (int)($_GET['id'] ?? 0);

if ($newsId <= 0 || !$db) {
    header("Location: news.php");
    exit;
}

aitf_ensure_news_schema($db);

$article = null;
try {
    // Fetch article
    $stmt = $db->prepare("SELECT * FROM news_articles WHERE id = :id AND status = 'published' LIMIT 1");
    $stmt->execute([':id' => $newsId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('news_detail.php fetch article error: ' . $e->getMessage());
}

if (!$article) {
    header("Location: news.php");
    exit;
}

// Increment view count
try {
    $db->prepare("UPDATE news_articles SET views_count = views_count + 1 WHERE id = :id")->execute([':id' => $newsId]);
} catch (Exception $e) {
    // Ignore view increment error
}

$recentNews = [];
try {
    // Fetch recent articles for sidebar
    $recentStmt = $db->prepare("SELECT id, title, category, created_at, image_path FROM news_articles WHERE id != :id AND status = 'published' ORDER BY created_at DESC LIMIT 5");
    $recentStmt->execute([':id' => $newsId]);
    $recentNews = $recentStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log('news_detail.php recent query error: ' . $e->getMessage());
    $recentNews = [];
}

$pageTitle = htmlspecialchars($article['title']) . " - AITF News";
$pageDescription = htmlspecialchars($article['summary'] ?: mb_substr(strip_tags($article['content']), 0, 160));
$canonicalUrl = "https://www.aitf.co.in/news_detail.php?id=" . urlencode($newsId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --aitf-blue: #0d4d8b;
            --aitf-navy: #052c54;
            --aitf-orange: #f5821f;
            --aitf-bg: #f8fafc;
            --aitf-border: #e2e8f0;
            --aitf-text: #1e293b;
            --aitf-muted: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--aitf-bg);
            color: var(--aitf-text);
            margin: 0;
            padding: 0;
        }

        .clean-header-section {
            padding: 175px 0 60px;
            background: linear-gradient(135deg, #0d4d8b 0%, #052c54 100%);
            color: #ffffff;
            position: relative;
        }

        .breadcrumb-line {
            font-size: 0.85rem;
            margin-bottom: 12px;
            color: #cbd5e1;
        }

        .breadcrumb-line a {
            color: #cbd5e1;
            text-decoration: none;
        }

        .breadcrumb-line a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        .breadcrumb-line span {
            margin: 0 6px;
            color: #94a3b8;
        }

        .detail-article-title {
            font-size: 2.1rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.35;
            margin: 0 0 14px;
        }

        .meta-tags-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 0.86rem;
            color: #cbd5e1;
        }

        .clean-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-circular { background: #fee2e2; color: #b91c1c; }
        .badge-process { background: #dcfce7; color: #15803d; }
        .badge-guideline { background: #fef3c7; color: #b45309; }
        .badge-iso { background: #e0f2fe; color: #0369a1; }
        .badge-default { background: #f1f5f9; color: #475569; }

        .article-content-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        .article-main-card {
            background: #ffffff;
            border: 1px solid var(--aitf-border);
            border-radius: 12px;
            padding: 35px 35px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 40px;
        }

        .article-featured-img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid var(--aitf-border);
        }

        .article-summary-lead {
            background: #f8fafc;
            border-left: 4px solid var(--aitf-blue);
            padding: 15px 20px;
            border-radius: 6px;
            font-size: 1rem;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 25px;
            font-style: italic;
        }

        .article-body-text {
            font-size: 1rem;
            line-height: 1.8;
            color: #334155;
        }

        .article-body-text h2, 
        .article-body-text h3 {
            color: #0f172a;
            font-weight: 700;
            margin-top: 25px;
            margin-bottom: 12px;
        }

        .article-body-text p {
            margin-bottom: 18px;
        }

        .article-body-text ul,
        .article-body-text ol {
            padding-left: 22px;
            margin-bottom: 20px;
        }

        .article-body-text li {
            margin-bottom: 8px;
        }

        .clean-attachment-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-download-pdf {
            background: #16a34a;
            color: #ffffff !important;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-download-pdf:hover {
            background: #15803d;
            text-decoration: none;
        }

        .sidebar-card-clean {
            background: #ffffff;
            border: 1px solid var(--aitf-border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        .sidebar-card-clean h4 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0 0 18px;
            color: #0f172a;
            border-bottom: 2px solid var(--aitf-blue);
            padding-bottom: 8px;
        }

        .recent-item-clean {
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .recent-item-clean:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .recent-item-clean a {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            text-decoration: none;
            line-height: 1.4;
            display: block;
            margin-bottom: 4px;
        }

        .recent-item-clean a:hover {
            color: var(--aitf-blue);
        }

        .recent-item-date {
            font-size: 0.78rem;
            color: var(--aitf-muted);
        }

        @media (max-width: 768px) {
            .clean-header-section { padding: 140px 0 45px; }
            .detail-article-title { font-size: 1.6rem; }
            .article-main-card { padding: 22px 18px; }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <!-- Header -->
    <?php include './headerSource.php'; ?>

    <!-- Clean Header Banner -->
    <div class="clean-header-section">
        <div class="container">
            <div class="breadcrumb-line">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="news.php">News &amp; Circulars</a>
                <span>/</span>
                <span><?php echo htmlspecialchars($article['category']); ?></span>
            </div>
            <h1 class="detail-article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="meta-tags-line">
                <span class="clean-badge badge-process"><?php echo htmlspecialchars($article['category']); ?></span>
                <span><i class="fa fa-calendar-o mr-1"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                <?php if (!empty($article['author'])) { ?>
                    <span><i class="fa fa-user-circle-o mr-1"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                <?php } ?>
                <span><i class="fa fa-eye mr-1"></i> <?php echo (int)$article['views_count']; ?> views</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="article-content-container">
        <div class="row">
            <!-- Article Body -->
            <div class="col-lg-8">
                <div class="article-main-card">
                    <?php if (!empty($article['image_path']) && file_exists(__DIR__ . '/' . $article['image_path'])) { ?>
                        <img src="<?php echo htmlspecialchars($article['image_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-featured-img">
                    <?php } ?>

                    <?php if (!empty($article['summary'])) { ?>
                        <div class="article-summary-lead">
                            <?php echo htmlspecialchars($article['summary']); ?>
                        </div>
                    <?php } ?>

                    <div class="article-body-text">
                        <?php echo $article['content']; ?>
                    </div>

                    <?php if (!empty($article['document_path'])) { ?>
                        <div class="clean-attachment-card">
                            <div>
                                <strong style="color: #15803d; font-size: 0.95rem;"><i class="fa fa-file-pdf-o text-danger mr-1"></i> Attached Document</strong>
                                <div style="font-size: 0.85rem; color: #475569; margin-top: 2px;">
                                    <?php echo htmlspecialchars($article['document_name'] ?: 'Official Circular Document.pdf'); ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($article['document_path']); ?>" target="_blank" download class="btn-download-pdf">
                                <i class="fa fa-download"></i> Download PDF
                            </a>
                        </div>
                    <?php } ?>

                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--aitf-border); display: flex; justify-content: space-between; align-items: center;">
                        <a href="news.php" style="color: var(--aitf-blue); font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                            <i class="fa fa-arrow-left mr-1"></i> Back to News
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');">
                            <i class="fa fa-share-alt mr-1"></i> Share
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-card-clean">
                    <h4>Recent Updates</h4>
                    <?php if (!empty($recentNews)) { ?>
                        <?php foreach ($recentNews as $rItem) { ?>
                            <div class="recent-item-clean">
                                <a href="news_detail.php?id=<?php echo urlencode($rItem['id']); ?>">
                                    <?php echo htmlspecialchars($rItem['title']); ?>
                                </a>
                                <span class="recent-item-date">
                                    <i class="fa fa-calendar-o mr-1"></i> <?php echo date('d M Y', strtotime($rItem['created_at'])); ?>
                                </span>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p style="color: var(--aitf-muted); font-size: 0.85rem; margin: 0;">No other updates yet.</p>
                    <?php } ?>
                </div>

                <div class="sidebar-card-clean" style="background: #0d4d8b; color: #ffffff; border: none;">
                    <h4 style="color: #ffffff; border-bottom-color: rgba(255,255,255,0.2);">Email Notifications</h4>
                    <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 15px;">
                        Subscribe to get immediate email alerts whenever new guidelines or circulars are released.
                    </p>
                    <a href="news.php#subscribeSection" class="btn btn-block" style="background: var(--aitf-orange); color: #ffffff; font-weight: 600; font-size: 0.88rem; border-radius: 6px;">
                        Subscribe to Alerts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include './footerSource.php'; ?>

<?php
$pageTitle = "News & Circulars - AITF";
$pageDescription = "Official circulars, certification process changes, guideline updates, and announcements from AITF Management Certification.";
$canonicalUrl = "https://www.aitf.co.in/news";

require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/include/news_helper.php';

// Fetch categories for filtering
$selectedCategory = trim($_GET['category'] ?? '');
$searchQuery = trim($_GET['search'] ?? '');

$newsList = [];
$categories = [];

if ($db) {
    aitf_ensure_news_schema($db);

    $sql = "SELECT * FROM news_articles WHERE status = 'published'";
    $params = [];

    if (!empty($selectedCategory) && $selectedCategory !== 'all') {
        $sql .= " AND category = :category";
        $params[':category'] = $selectedCategory;
    }

    if (!empty($searchQuery)) {
        $sql .= " AND (title LIKE :query OR summary LIKE :query OR content LIKE :query)";
        $params[':query'] = '%' . $searchQuery . '%';
    }

    $sql .= " ORDER BY is_featured DESC, created_at DESC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log('news.php query error: ' . $e->getMessage());
        $newsList = [];
    }

    try {
        // Fetch distinct categories for filter buttons
        $catStmt = $db->query("SELECT DISTINCT category FROM news_articles WHERE status = 'published' AND category IS NOT NULL AND category != ''");
        $categories = $catStmt ? ($catStmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];
    } catch (Exception $e) {
        error_log('news.php categories query error: ' . $e->getMessage());
        $categories = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
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

        /* Clean Header Section */
        .clean-header-section {
            padding: 175px 0 65px;
            background: linear-gradient(135deg, #0d4d8b 0%, #052c54 100%);
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .clean-header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 10px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .clean-header-section p {
            font-size: 1.05rem;
            color: #cbd5e1;
            margin: 0 auto;
            max-width: 600px;
            font-weight: 400;
        }

        /* Main Container & Filter Row */
        .news-page-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        .filter-search-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--aitf-border);
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .cat-tab-btn {
            display: inline-block;
            padding: 7px 18px;
            border-radius: 20px;
            background: #ffffff;
            color: var(--aitf-muted);
            border: 1px solid var(--aitf-border);
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .cat-tab-btn:hover {
            border-color: var(--aitf-blue);
            color: var(--aitf-blue);
            text-decoration: none;
        }

        .cat-tab-btn.active {
            background: var(--aitf-blue);
            color: #ffffff;
            border-color: var(--aitf-blue);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(13, 77, 139, 0.2);
            text-decoration: none;
        }

        .search-box-clean {
            position: relative;
            min-width: 260px;
        }

        .search-box-clean i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .search-box-clean input {
            width: 100%;
            height: 38px;
            padding: 6px 14px 6px 36px;
            border-radius: 20px;
            border: 1px solid var(--aitf-border);
            background: #ffffff;
            font-size: 0.88rem;
            color: var(--aitf-text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box-clean input:focus {
            border-color: var(--aitf-blue);
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
        }

        /* Clean News Feed Cards */
        .clean-news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        .clean-news-card {
            background: #ffffff;
            border: 1px solid var(--aitf-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .clean-news-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(13, 77, 139, 0.08);
            border-color: #cbd5e1;
        }

        .card-img-thumb {
            height: 160px;
            background: #f1f5f9;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-img-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body-clean {
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-meta-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--aitf-muted);
            margin-bottom: 10px;
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

        .card-headline {
            font-size: 1.12rem;
            font-weight: 600;
            line-height: 1.4;
            margin: 0 0 10px;
            color: #0f172a;
        }

        .card-headline a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .card-headline a:hover {
            color: var(--aitf-blue);
        }

        .card-excerpt {
            font-size: 0.88rem;
            color: #475569;
            line-height: 1.55;
            margin: 0 0 16px;
            flex-grow: 1;
        }

        .card-footer-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
            margin-top: auto;
        }

        .link-read-more {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--aitf-blue);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .link-read-more:hover {
            color: var(--aitf-orange);
            text-decoration: none;
        }

        .pill-doc-download {
            font-size: 0.76rem;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pill-doc-download:hover {
            background: #dbeafe;
            text-decoration: none;
            color: #1d4ed8;
        }

        /* Clean Subscription Box with Notification Icon */
        .clean-subscribe-box {
            background: #ffffff;
            border: 1px solid var(--aitf-border);
            border-top: 4px solid var(--aitf-orange);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 6px 30px rgba(13, 77, 139, 0.06);
            margin-top: 25px;
            position: relative;
            text-align: center;
        }

        .notif-icon-circle {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            background: #fff7ed;
            color: var(--aitf-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            position: relative;
            box-shadow: 0 4px 15px rgba(245, 130, 31, 0.2);
            border: 1px solid #fed7aa;
        }

        .notif-icon-circle .pulse-ring {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 14px;
            height: 14px;
            background: #ef4444;
            border: 2px solid #ffffff;
            border-radius: 50%;
        }

        .sub-header-row {
            max-width: 560px;
            margin: 0 auto 24px;
        }

        .notif-mini-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--aitf-blue);
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 3px 12px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .sub-header-row h3 {
            font-size: 1.45rem;
            font-weight: 700;
            margin: 0 0 8px;
            color: #0f172a;
        }

        .sub-header-row p {
            font-size: 0.92rem;
            color: var(--aitf-muted);
            line-height: 1.55;
            margin: 0;
        }

        .sub-form-wrap {
            max-width: 500px;
            margin: 0 auto;
        }

        .sub-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid var(--aitf-border);
            border-radius: 30px;
            padding: 4px 4px 4px 16px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .sub-input-wrap:focus-within {
            border-color: var(--aitf-blue);
            box-shadow: 0 0 0 4px rgba(13, 77, 139, 0.1);
        }

        .notif-input-icon {
            color: #94a3b8;
            font-size: 1.05rem;
            margin-right: 10px;
        }

        .sub-email-field {
            flex-grow: 1;
            height: 40px;
            border: none;
            background: transparent;
            font-size: 0.92rem;
            color: var(--aitf-text);
            outline: none;
            padding: 0;
        }

        .btn-sub-action {
            height: 42px;
            padding: 0 24px;
            background: linear-gradient(135deg, #f5821f, #e06d0b);
            color: #ffffff !important;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            box-shadow: 0 3px 10px rgba(245, 130, 31, 0.3);
        }

        .btn-sub-action:hover {
            background: linear-gradient(135deg, #ff9538, #f5821f);
            box-shadow: 0 5px 15px rgba(245, 130, 31, 0.45);
            transform: translateY(-1px);
        }

        .otp-box-clean {
            display: none;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 22px;
            margin-top: 15px;
            text-align: center;
        }

        .otp-input-clean {
            width: 170px;
            height: 44px;
            text-align: center;
            font-size: 1.4rem;
            letter-spacing: 6px;
            font-weight: 700;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            outline: none;
            margin-right: 8px;
            background: #ffffff;
            color: var(--aitf-blue);
        }

        .otp-input-clean:focus {
            border-color: var(--aitf-blue);
            box-shadow: 0 0 0 3px rgba(13, 77, 139, 0.1);
        }

        .empty-clean-state {
            text-align: center;
            padding: 50px 20px;
            background: #ffffff;
            border: 1px dashed var(--aitf-border);
            border-radius: 12px;
        }

        @media (max-width: 768px) {
            .clean-header-section { padding: 140px 0 50px; }
            .clean-header-section h1 { font-size: 1.9rem; }
            .clean-news-grid { grid-template-columns: 1fr; }
            .filter-search-bar { flex-direction: column; align-items: stretch; }
            .search-box-clean { width: 100%; }
            .sub-input-wrap { flex-direction: column; padding: 12px; border-radius: 14px; gap: 10px; }
            .notif-input-icon { display: none; }
            .sub-email-field { width: 100%; text-align: center; }
            .btn-sub-action { width: 100%; justify-content: center; border-radius: 10px; }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <!-- Header -->
    <?php include './headerSource.php'; ?>

    <!-- Clean Header Banner -->
    <div class="clean-header-section">
        <div class="container">
            <h1>News &amp; Circulars</h1>
            <p>Official notices, certification process updates, and regulatory circulars from AITF.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="news-page-container">
        <!-- Controls: Filters + Search -->
        <div class="filter-search-bar">
            <div class="category-tabs">
                <a href="news.php" class="cat-tab-btn <?php echo empty($selectedCategory) ? 'active' : ''; ?>">All Updates</a>
                <?php foreach ($categories as $cat) { 
                    $activeClass = ($selectedCategory === $cat) ? 'active' : '';
                ?>
                    <a href="news.php?category=<?php echo urlencode($cat); ?>" class="cat-tab-btn <?php echo $activeClass; ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php } ?>
            </div>

            <div class="search-box-clean">
                <form method="GET" action="news.php" style="margin:0;">
                    <?php if (!empty($selectedCategory)) { ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <?php } ?>
                    <i class="fa fa-search"></i>
                    <input type="text" name="search" placeholder="Search updates..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                </form>
            </div>
        </div>

        <!-- News Cards -->
        <?php if (!empty($newsList)) { ?>
            <div class="clean-news-grid">
                <?php foreach ($newsList as $item) { 
                    $createdDate = date('d M Y', strtotime($item['created_at']));
                    $badgeClass = 'badge-default';
                    $catLower = strtolower($item['category']);
                    if (strpos($catLower, 'circular') !== false) {
                        $badgeClass = 'badge-circular';
                    } elseif (strpos($catLower, 'process') !== false) {
                        $badgeClass = 'badge-process';
                    } elseif (strpos($catLower, 'guideline') !== false) {
                        $badgeClass = 'badge-guideline';
                    } elseif (strpos($catLower, 'iso') !== false) {
                        $badgeClass = 'badge-iso';
                    }

                    $detailUrl = "news_detail.php?id=" . urlencode($item['id']);
                ?>
                    <div class="clean-news-card">
                        <?php if (!empty($item['image_path']) && file_exists(__DIR__ . '/' . $item['image_path'])) { ?>
                            <div class="card-img-thumb">
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                        <?php } ?>

                        <div class="card-body-clean">
                            <div class="card-meta-line">
                                <span class="clean-badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                                <span><i class="fa fa-calendar-o mr-1"></i> <?php echo $createdDate; ?></span>
                            </div>

                            <h3 class="card-headline">
                                <a href="<?php echo $detailUrl; ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </h3>

                            <p class="card-excerpt">
                                <?php 
                                    if (!empty($item['summary'])) {
                                        echo htmlspecialchars($item['summary']);
                                    } else {
                                        echo htmlspecialchars(mb_substr(strip_tags($item['content']), 0, 120)) . '...';
                                    }
                                ?>
                            </p>

                            <div class="card-footer-line">
                                <a href="<?php echo $detailUrl; ?>" class="link-read-more">
                                    Read Details <i class="fa fa-angle-right"></i>
                                </a>

                                <?php if (!empty($item['document_path'])) { ?>
                                    <a href="<?php echo htmlspecialchars($item['document_path']); ?>" target="_blank" download class="pill-doc-download">
                                        <i class="fa fa-file-pdf-o text-danger"></i> PDF
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-clean-state">
                <i class="fa fa-info-circle" style="font-size: 32px; color: #94a3b8; margin-bottom: 10px;"></i>
                <h4 style="color: #334155; font-size: 1.1rem; margin-bottom: 5px;">No updates found</h4>
                <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
                    <?php if (!empty($searchQuery) || !empty($selectedCategory)) { ?>
                        No articles match the current filter. <a href="news.php" style="color: var(--aitf-blue); font-weight: 500;">View all</a>
                    <?php } else { ?>
                        New circulars and advisories will be published here.
                    <?php } ?>
                </p>
            </div>
        <?php } ?>

        <!-- Clean Compact Subscribe Box with Notification Icon -->
        <div class="clean-subscribe-box" id="subscribeSection">
            <div class="notif-icon-circle">
                <i class="fa fa-bell-o"></i>
                <span class="pulse-ring"></span>
            </div>

            <div class="sub-header-row">
                <span class="notif-mini-tag">
                    <i class="fa fa-envelope-o"></i> Email Notifications
                </span>
                <h3>Subscribe to AITF Alerts</h3>
                <p>Get immediate email notifications whenever AITF releases new circulars, advisories, or process updates.</p>
            </div>

            <div class="sub-form-wrap">
                <!-- Step 1: Send OTP -->
                <div id="subStep1">
                    <div class="sub-input-wrap">
                        <i class="fa fa-envelope-o notif-input-icon"></i>
                        <input type="email" id="subEmailInput" class="sub-email-field" placeholder="Enter your email address..." required>
                        <button type="button" id="btnSendSubOtp" class="btn-sub-action">
                            <i class="fa fa-paper-plane-o"></i> <span>Send OTP</span>
                        </button>
                    </div>
                    <div id="subFeedback" style="margin-top: 12px; font-size: 0.88rem; text-align: center; display: none;"></div>
                </div>

                <!-- Step 2: Verify OTP -->
                <div id="subStep2" class="otp-box-clean">
                    <p style="margin: 0 0 12px; font-size: 0.9rem; color: #334155; font-weight: 500;">
                        <i class="fa fa-shield text-warning mr-1"></i> Enter the 6-digit OTP sent to <strong id="displaySubEmail" style="color: var(--aitf-blue);"></strong>:
                    </p>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                        <input type="text" id="subOtpInput" class="otp-input-clean" maxlength="6" placeholder="------" pattern="\d*">
                        <button type="button" id="btnVerifySubOtp" class="btn-sub-action" style="height: 44px;">
                            <i class="fa fa-check-circle"></i> <span>Verify &amp; Subscribe</span>
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #64748b; max-width: 320px; margin: 0 auto;">
                        <button type="button" id="btnResendSubOtp" class="btn btn-link" style="padding: 0; font-size: 0.82rem; color: #64748b;" disabled>
                            Resend OTP (<span id="resendCountdown">60</span>s)
                        </button>
                        <button type="button" id="btnChangeSubEmail" class="btn btn-link" style="padding: 0; font-size: 0.82rem; color: #64748b;">
                            Change Email
                        </button>
                    </div>
                    <div id="subVerifyFeedback" style="margin-top: 10px; font-size: 0.88rem; text-align: center; display: none;"></div>
                </div>

                <!-- Step 3: Success -->
                <div id="subSuccessBox" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; padding: 20px; border-radius: 12px; text-align: center;">
                    <h5 style="color: #16a34a; margin: 0 0 6px; font-size: 1.1rem; font-weight: 700;">
                        <i class="fa fa-check-circle"></i> Subscribed Successfully!
                    </h5>
                    <p style="color: #166534; font-size: 0.9rem; margin: 0;">
                        Your email has been verified. You will receive real-time alerts on future AITF announcements.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var resendTimer = null;
            var countdown = 60;

            function startCountdown() {
                clearInterval(resendTimer);
                countdown = 60;
                $('#resendCountdown').text(countdown);
                $('#btnResendSubOtp').prop('disabled', true).css('opacity', '0.6');

                resendTimer = setInterval(function() {
                    countdown--;
                    $('#resendCountdown').text(countdown);
                    if (countdown <= 0) {
                        clearInterval(resendTimer);
                        $('#btnResendSubOtp').prop('disabled', false).css('opacity', '1').text('Resend OTP');
                    }
                }, 1000);
            }

            // Send OTP
            $('#btnSendSubOtp, #btnResendSubOtp').on('click', function(e) {
                e.preventDefault();
                var email = $.trim($('#subEmailInput').val());

                if (!email || !email.includes('@')) {
                    $('#subFeedback').css('color', '#dc2626').text('Please enter a valid email address.').fadeIn();
                    return;
                }

                var $btn = $(this);
                var origText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
                $('#subFeedback').hide();

                $.ajax({
                    url: 'ajax_news_subscribe_otp.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { email: email },
                    success: function(resp) {
                        $btn.prop('disabled', false).html(origText);
                        if (resp.success) {
                            $('#displaySubEmail').text(email);
                            $('#subStep1').hide();
                            $('#subStep2').fadeIn();
                            $('#subOtpInput').val('').focus();
                            startCountdown();
                        } else {
                            $('#subFeedback').css('color', '#dc2626').text(resp.message || 'Error sending OTP.').fadeIn();
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(origText);
                        $('#subFeedback').css('color', '#dc2626').text('Network error. Please try again.').fadeIn();
                    }
                });
            });

            // Change Email
            $('#btnChangeSubEmail').on('click', function() {
                clearInterval(resendTimer);
                $('#subStep2').hide();
                $('#subStep1').fadeIn();
                $('#subEmailInput').focus();
            });

            // Verify OTP
            $('#btnVerifySubOtp').on('click', function(e) {
                e.preventDefault();
                var email = $.trim($('#subEmailInput').val());
                var otp = $.trim($('#subOtpInput').val());

                if (otp.length !== 6) {
                    $('#subVerifyFeedback').css('color', '#dc2626').text('Please enter the 6-digit OTP.').fadeIn();
                    return;
                }

                var $btn = $(this);
                var origText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $('#subVerifyFeedback').hide();

                $.ajax({
                    url: 'ajax_news_subscribe_verify.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { email: email, otp: otp },
                    success: function(resp) {
                        $btn.prop('disabled', false).html(origText);
                        if (resp.success) {
                            $('#subStep2').hide();
                            $('#subSuccessBox').fadeIn();
                        } else {
                            $('#subVerifyFeedback').css('color', '#dc2626').text(resp.message || 'Verification failed.').fadeIn();
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(origText);
                        $('#subVerifyFeedback').css('color', '#dc2626').text('Network error. Please try again.').fadeIn();
                    }
                });
            });
        });
    </script>

    <!-- Footer -->
    <?php include './footerSource.php'; ?>

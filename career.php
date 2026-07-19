<?php
$recaptchaHelperPath = __DIR__ . '/include/recaptcha_helper.php';
if (file_exists($recaptchaHelperPath)) {
    require_once $recaptchaHelperPath;
}

$recaptchaConfig = function_exists('aitf_get_recaptcha_config') ? aitf_get_recaptcha_config() : [
    'site_key' => '',
    'version' => 'v2_checkbox',
    'action' => 'submit',
    'theme' => 'light',
    'enabled' => false
];
$recaptchaReady = function_exists('aitf_is_recaptcha_ready') ? aitf_is_recaptcha_ready() : false;
$recaptchaBypass = function_exists('aitf_should_bypass_recaptcha') ? aitf_should_bypass_recaptcha() : false;
$recaptchaFrontendEnabled = $recaptchaReady && !$recaptchaBypass;

$errorMessage = isset($_GET['error']) ? trim($_GET['error']) : '';
$successApplicant = isset($_GET['success']) && $_GET['success'] == '1' ? trim($_GET['applicant'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <?php if ($recaptchaFrontendEnabled): ?>
        <?php if (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v3'): ?>
            <script
                src="https://www.google.com/recaptcha/api.js?render=<?php echo urlencode($recaptchaConfig['site_key']); ?>"></script>
        <?php else: ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php endif; ?>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Careers - AITF Management Certification</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: #f5f8fc;
            color: #0f172a;
            line-height: 1.6;
        }

        .career-page {
            overflow: hidden;
            background: #f5f8fc;
        }

        .career-hero {
            min-height: 720px;
            padding: 185px 20px 90px;
            background:
                radial-gradient(circle at 15% 18%, rgba(96, 165, 250, .35), transparent 28%),
                radial-gradient(circle at 86% 16%, rgba(45, 212, 191, .26), transparent 26%),
                linear-gradient(135deg, #071120 0%, #10223d 45%, #0f3b73 100%);
            color: #fff;
            position: relative;
        }

        .career-hero:before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, .06) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, .75), transparent 88%);
        }

        .career-container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, .8fr);
            gap: 34px;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 14px;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 999px;
            background: rgba(255, 255, 255, .09);
            color: #dbeafe;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            backdrop-filter: blur(12px);
        }

        .career-hero h1 {
            margin: 24px 0 18px;
            font-size: clamp(42px, 6vw, 78px);
            line-height: .96;
            letter-spacing: -3px;
            font-weight: 900;
        }

        .career-hero p {
            margin: 0;
            max-width: 690px;
            color: rgba(226, 232, 240, .9);
            font-size: 18px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 34px;
        }

        .career-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 999px;
            padding: 14px 22px;
            font-weight: 900;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            cursor: pointer;
        }

        .career-btn.primary {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            color: #fff;
            box-shadow: 0 22px 42px rgba(37, 99, 235, .32);
        }

        .career-btn.secondary {
            background: rgba(255, 255, 255, .11);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .18);
        }

        .career-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .hero-card {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 32px;
            padding: 28px;
            backdrop-filter: blur(18px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, .28);
        }

        .hero-card h3 {
            margin: 0 0 16px;
            color: #fff;
            font-size: 24px;
            font-weight: 900;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .hero-stat {
            border-radius: 22px;
            padding: 18px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .14);
        }

        .hero-stat strong {
            display: block;
            font-size: 28px;
            color: #fff;
            line-height: 1;
        }

        .hero-stat span {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 700;
        }

        .section {
            padding: 96px 0;
        }

        .section-title {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 46px;
        }

        .section-title .tag {
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: .12em;
            font-weight: 900;
            font-size: 12px;
        }

        .section-title h2 {
            margin: 10px 0 12px;
            font-size: clamp(32px, 4vw, 50px);
            line-height: 1.08;
            letter-spacing: -1.8px;
            font-weight: 900;
        }

        .section-title p {
            color: #64748b;
            font-size: 16px;
            margin: 0;
        }

        .why-grid,
        .love-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .career-clean-section {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .career-clean-section.alt {
            background: linear-gradient(180deg, #f2f7ff 0%, #ffffff 100%);
        }

        .career-clean-section:before {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: rgba(37, 99, 235, .08);
            top: -140px;
            right: -120px;
        }

        .career-section-header {
            max-width: 880px;
            margin: 0 auto 42px;
            text-align: center;
        }

        .career-section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #eaf2ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .career-section-header h2 {
            margin: 16px 0 14px;
            color: #0f172a;
            font-size: clamp(32px, 4.2vw, 50px);
            line-height: 1.12;
            letter-spacing: -1.4px;
            font-weight: 900;
        }

        .career-section-header p {
            margin: 0 auto;
            max-width: 760px;
            color: #475569;
            font-size: 17px;
            line-height: 1.8;
            font-weight: 500;
        }

        .feature-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 26px;
            padding: 30px;
            box-shadow: 0 20px 52px rgba(15, 23, 42, .08);
            transition: transform .2s ease, box-shadow .2s ease;
            min-height: 235px;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 70px rgba(15, 23, 42, .12);
        }

        .feature-icon {
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            color: #2563eb;
            font-size: 23px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }

        .feature-card p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
        }

        .impact-section {
            background: linear-gradient(135deg, #0f172a, #17325f);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .impact-section:before {
            content: "";
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: rgba(96, 165, 250, .22);
            right: -180px;
            top: -150px;
            filter: blur(8px);
        }

        .impact-card {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 34px;
            align-items: center;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 34px;
            padding: 42px;
            backdrop-filter: blur(12px);
        }

        .impact-card h2 {
            margin: 0 0 14px;
            font-size: clamp(34px, 4vw, 54px);
            font-weight: 900;
            letter-spacing: -1.5px;
        }

        .impact-card p {
            margin: 0;
            color: #cbd5e1;
            font-size: 16px;
        }

        .impact-list {
            display: grid;
            gap: 14px;
        }

        .impact-list div {
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 18px;
            padding: 15px 18px;
            color: #e2e8f0;
            font-weight: 750;
        }

        .director-wrap {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
            align-items: stretch;
        }

        .director-card {
            border-radius: 30px;
            background: linear-gradient(160deg, #2563eb, #0f172a);
            color: #fff;
            padding: 34px;
            box-shadow: 0 26px 70px rgba(37, 99, 235, .22);
        }

        .director-avatar {
            width: 92px;
            height: 92px;
            border-radius: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
            font-size: 40px;
            margin-bottom: 20px;
        }

        .director-card h3 {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
        }

        .director-card span {
            color: #bfdbfe;
            font-weight: 800;
        }

        .director-message {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 34px;
            box-shadow: 0 20px 52px rgba(15, 23, 42, .08);
            position: relative;
        }

        .director-message:before {
            content: "“";
            position: absolute;
            right: 26px;
            top: -22px;
            font-size: 110px;
            color: #dbeafe;
            font-family: Georgia, serif;
            line-height: 1;
        }

        .director-message p {
            position: relative;
            margin: 0 0 16px;
            color: #334155;
            font-size: 17px;
        }

        .apply-section {
            padding-top: 30px;
        }

        .apply-grid {
            display: grid;
            grid-template-columns: minmax(0, .85fr) minmax(360px, 1fr);
            gap: 28px;
            align-items: start;
        }

        .apply-info {
            background: #0f172a;
            color: #fff;
            border-radius: 32px;
            padding: 36px;
            position: sticky;
            top: 110px;
            box-shadow: 0 24px 72px rgba(15, 23, 42, .18);
        }

        .apply-info h2 {
            margin: 0 0 12px;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: -1px;
        }

        .apply-info p {
            color: #cbd5e1;
            margin: 0 0 22px;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 8px 8px 0 0;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #e2e8f0;
            font-weight: 800;
        }

        .career-form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 32px;
            padding: 34px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .1);
        }

        .form-alert {
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 18px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-weight: 700;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            padding: 14px 15px;
            font: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .field textarea {
            min-height: 112px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .11);
        }

        .captcha-box {
            margin: 4px 0 20px;
            padding: 14px;
            border-radius: 18px;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
        }

        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 18px;
            padding: 16px 22px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            font-weight: 900;
            font-size: 16px;
            box-shadow: 0 18px 34px rgba(37, 99, 235, .24);
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(37, 99, 235, .30);
        }

        @media (max-width: 980px) {

            .hero-grid,
            .impact-card,
            .director-wrap,
            .apply-grid {
                grid-template-columns: 1fr;
            }

            .why-grid,
            .love-grid {
                grid-template-columns: 1fr 1fr;
            }

            .apply-info {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .career-hero {
                padding-top: 150px;
            }

            .why-grid,
            .love-grid,
            .form-row {
                grid-template-columns: 1fr;
            }

            .career-form-card,
            .apply-info,
            .impact-card,
            .director-card,
            .director-message {
                padding: 24px;
                border-radius: 24px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <style>
        #home .top-header:not(.scrolled) {
            --nav-bg: transparent;
            --nav-text: #f1f5f9;
            --nav-hover-text: #ffffff;
            --nav-hover-bg: rgba(255, 255, 255, 0.12);
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        #home .top-header:not(.scrolled) .navbar a:not(.login-btn),
        #home .top-header:not(.scrolled) .navbar .dropdown>a,
        #home .top-header:not(.scrolled) .nav-primary .nav-link,
        #home .top-header:not(.scrolled) .header-nav .nav-link:not(.login-btn) {
            color: #f1f5f9 !important;
        }

        #home .top-header:not(.scrolled) .mobile-toggle-bar {
            background-color: #f1f5f9 !important;
        }

        .career-page {
            --career-ink: #132033;
            --career-muted: #526173;
            --career-primary: #2563eb;
            --career-primary-dark: #123f8f;
            --career-accent: #38bdf8;
            --career-soft: #eef6ff;
            --career-card-shadow: 0 24px 70px rgba(15, 23, 42, .10);
            background:
                radial-gradient(circle at 8% 14%, rgba(59, 130, 246, .10), transparent 28%),
                radial-gradient(circle at 92% 34%, rgba(20, 184, 166, .10), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, #eef5ff 48%, #ffffff 100%) !important;
            color: var(--career-ink) !important;
            font-family: 'Inter', Arial, sans-serif !important;
        }

        .career-page .career-hero {
            min-height: auto !important;
            padding: 168px 20px 104px !important;
            background:
                linear-gradient(115deg, rgba(6, 17, 38, .96) 0%, rgba(16, 45, 91, .94) 52%, rgba(19, 103, 140, .90) 100%),
                radial-gradient(circle at 80% 15%, rgba(96, 165, 250, .42), transparent 32%) !important;
            isolation: isolate;
        }

        .career-page .career-hero:before {
            opacity: .45;
            mask-image: linear-gradient(180deg, #000 0%, transparent 95%) !important;
        }

        .career-page .career-hero:after {
            content: "";
            position: absolute;
            inset: auto -12% -170px -12%;
            height: 260px;
            background: #f8fbff;
            border-radius: 50% 50% 0 0;
            z-index: 0;
        }

        .career-page .hero-grid {
            grid-template-columns: minmax(0, 1.05fr) minmax(340px, .82fr) !important;
            gap: clamp(28px, 5vw, 58px) !important;
        }

        .career-page .eyebrow {
            background: rgba(255, 255, 255, .14) !important;
            border: 1px solid rgba(255, 255, 255, .26) !important;
            color: #eff6ff !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .18), 0 14px 32px rgba(0, 0, 0, .12);
        }

        .career-page .career-hero h1,
        .career-page .impact-card h2,
        .career-page .apply-info h2 {
            color: #ffffff !important;
            text-shadow: 0 16px 42px rgba(0, 0, 0, .34);
        }

        .career-page .career-hero h1 {
            max-width: 820px;
            font-size: clamp(46px, 6.4vw, 86px) !important;
            line-height: .98 !important;
            letter-spacing: -3.2px !important;
            margin-bottom: 22px !important;
        }

        .career-page .career-hero p {
            color: #f1f5f9 !important;
            font-size: clamp(18px, 1.5vw, 22px) !important;
            line-height: 1.85 !important;
            max-width: 760px !important;
            text-shadow: 0 4px 16px rgba(0, 0, 0, .3);
            font-weight: 500;
        }

        .career-page .career-btn {
            min-height: 52px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .18);
        }

        .career-page .career-btn.primary {
            background: linear-gradient(135deg, #38bdf8, #2563eb 58%, #1d4ed8) !important;
        }

        .career-page .career-btn.secondary {
            background: rgba(255, 255, 255, .16) !important;
            color: #ffffff !important;
        }

        .career-page .hero-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .20), rgba(255, 255, 255, .10)) !important;
            border: 1px solid rgba(255, 255, 255, .28) !important;
            box-shadow: 0 34px 90px rgba(1, 10, 26, .34) !important;
            position: relative;
            overflow: hidden;
        }

        .career-page .hero-card:before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 0%, rgba(255, 255, 255, .26), transparent 36%);
            pointer-events: none;
        }

        .career-page .hero-card h3,
        .career-page .hero-stat strong {
            color: #ffffff !important;
            position: relative;
        }

        .career-page .hero-stat {
            background: rgba(255, 255, 255, .17) !important;
            border: 1px solid rgba(255, 255, 255, .20) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .18);
            position: relative;
        }

        .career-page .hero-stat span {
            color: #dbeafe !important;
        }

        .career-page .section {
            padding: 92px 0 !important;
        }

        .career-page .section-title h2,
        .career-page .feature-card h3,
        .career-page .director-card h3,
        .career-page .career-form-card h3 {
            color: var(--career-ink) !important;
        }

        .career-page .section-title p,
        .career-page .feature-card p,
        .career-page .director-message p {
            color: #475569 !important;
            line-height: 1.8 !important;
            font-size: 16px !important;
            font-weight: 500;
        }

        .career-page .feature-card p {
            font-size: 15.5px !important;
        }

        .career-page .feature-card,
        .career-page .director-message,
        .career-page .career-form-card {
            background: rgba(255, 255, 255, .94) !important;
            border: 1px solid rgba(148, 163, 184, .24) !important;
            box-shadow: var(--career-card-shadow) !important;
        }

        .career-page .feature-card {
            position: relative;
            overflow: hidden;
        }

        .career-page .feature-card:before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, var(--career-primary), var(--career-accent));
        }

        .career-page .feature-icon {
            background: linear-gradient(135deg, #e0f2fe, #dbeafe) !important;
            color: var(--career-primary) !important;
            box-shadow: 0 14px 28px rgba(37, 99, 235, .14);
        }

        .career-page .impact-section {
            background: linear-gradient(180deg, #f8fbff 0%, #eaf2ff 100%) !important;
            color: #ffffff !important;
            padding-top: 20px !important;
        }

        .career-page .impact-card {
            background:
                linear-gradient(135deg, rgba(12, 27, 52, .96), rgba(24, 67, 130, .92)),
                radial-gradient(circle at 86% 0%, rgba(56, 189, 248, .28), transparent 34%) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            box-shadow: 0 30px 90px rgba(15, 23, 42, .20) !important;
        }

        .career-page .impact-card p,
        .career-page .impact-list div {
            color: #f1f5f9 !important;
            font-size: 17px !important;
            font-weight: 500 !important;
            line-height: 1.8 !important;
        }

        .career-page .impact-card h2 {
            font-size: clamp(38px, 4.5vw, 56px) !important;
        }

        .career-page .impact-list div {
            background: rgba(255, 255, 255, .14) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            font-size: 16px;
        }

        .career-page .impact-list i {
            color: #86efac;
            margin-right: 8px;
        }

        .career-page .director-card {
            background:
                linear-gradient(145deg, rgba(37, 99, 235, .96), rgba(15, 23, 42, .98)),
                radial-gradient(circle at 0% 0%, rgba(56, 189, 248, .35), transparent 34%) !important;
        }

        .career-page .director-card h3 {
            color: #ffffff !important;
        }

        .career-page .apply-section {
            background: linear-gradient(180deg, #ffffff 0%, #eef5ff 100%) !important;
            padding-top: 78px !important;
        }

        .career-page .apply-info {
            background:
                linear-gradient(145deg, rgba(15, 23, 42, .98), rgba(30, 64, 175, .94)),
                radial-gradient(circle at 100% 0%, rgba(56, 189, 248, .25), transparent 32%) !important;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .career-page .apply-info p,
        .career-page .role-pill {
            color: #dbeafe !important;
        }

        .career-page .career-form-card {
            position: relative;
            overflow: hidden;
        }

        .career-page .career-form-card:before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 7px;
            background: linear-gradient(90deg, #2563eb, #38bdf8, #14b8a6);
        }

        .career-page .form-heading {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .career-page .form-heading-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            color: #ffffff;
            box-shadow: 0 16px 30px rgba(37, 99, 235, .24);
            flex: 0 0 auto;
            font-size: 22px;
        }

        .career-page .form-heading h3 {
            margin: 0 0 4px;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -.4px;
        }

        .career-page .form-heading p {
            margin: 0;
            color: var(--career-muted);
        }

        .career-page .field label {
            color: #24324a !important;
        }

        .career-page .field input,
        .career-page .field select,
        .career-page .field textarea {
            background: #ffffff !important;
            border-color: #cbd8e8 !important;
            box-shadow: 0 1px 0 rgba(15, 23, 42, .03);
        }

        .career-page .field input:focus,
        .career-page .field select:focus,
        .career-page .field textarea:focus {
            border-color: var(--career-primary) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .13) !important;
        }

        .career-page .submit-btn {
            background: linear-gradient(135deg, #1d4ed8, #2563eb 55%, #38bdf8) !important;
            min-height: 56px;
            letter-spacing: .01em;
        }

        .career-success-overlay {
            position: fixed;
            inset: 0;
            z-index: 5000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: careerFadeIn .22s ease both;
        }

        .career-success-card {
            width: min(560px, 100%);
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 42px;
            text-align: center;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(239, 246, 255, .98)),
                radial-gradient(circle at 50% 0%, rgba(56, 189, 248, .20), transparent 36%);
            border: 1px solid rgba(255, 255, 255, .72);
            box-shadow: 0 34px 110px rgba(0, 0, 0, .35);
            animation: careerPopIn .28s ease both;
        }

        .career-success-card:before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 8px;
            background: linear-gradient(90deg, #22c55e, #38bdf8, #2563eb);
        }

        .career-success-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            color: #ffffff;
            font-size: 34px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 20px 42px rgba(34, 197, 94, .28);
        }

        .career-success-kicker {
            margin: 0 0 8px;
            color: #2563eb;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .career-success-card h2 {
            margin: 0 0 12px;
            color: #0f172a !important;
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: -1px;
        }

        .career-success-card p:not(.career-success-kicker) {
            margin: 0 auto 26px;
            color: #475569;
            font-size: 16px;
            line-height: 1.75;
            max-width: 440px;
        }

        .career-success-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: #eaf2ff;
            color: #334155;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }

        .career-success-btn {
            border: 0;
            border-radius: 999px;
            padding: 13px 24px;
            color: #ffffff;
            font-weight: 900;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            box-shadow: 0 16px 34px rgba(37, 99, 235, .25);
            cursor: pointer;
        }

        .career-success-overlay.is-hiding {
            animation: careerFadeOut .2s ease both;
        }

        @keyframes careerFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes careerFadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        @keyframes careerPopIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 980px) {
            .career-page .career-hero {
                padding-top: 142px !important;
            }

            .career-page .hero-grid {
                grid-template-columns: 1fr !important;
            }

            .career-page .career-hero h1 {
                letter-spacing: -2px !important;
            }
        }

        @media (max-width: 640px) {
            .career-page .career-hero {
                padding: 126px 16px 78px !important;
            }

            .career-page .career-container {
                width: min(100% - 24px, 1180px) !important;
            }

            .career-page .career-hero h1 {
                font-size: clamp(38px, 12vw, 54px) !important;
                letter-spacing: -1.4px !important;
            }

            .career-page .hero-actions {
                gap: 10px !important;
            }

            .career-page .career-btn {
                width: 100%;
            }

            .career-success-card {
                padding: 34px 22px 28px;
                border-radius: 26px;
            }
        }
    </style>

    <div class="career-page">
        <?php if ($successApplicant !== ''): ?>
            <?php $safeApplicantName = htmlspecialchars($successApplicant, ENT_QUOTES, 'UTF-8'); ?>
            <div class="career-success-overlay" id="careerSuccessMessage" role="dialog" aria-modal="true"
                aria-labelledby="careerSuccessTitle">
                <div class="career-success-card">
                    <button class="career-success-close" type="button" data-career-success-close
                        aria-label="Close message">&times;</button>
                    <div class="career-success-icon"><i class="fa-solid fa-check"></i></div>
                    <p class="career-success-kicker">Application submitted</p>
                    <h2 id="careerSuccessTitle">Thank you, <?php echo $safeApplicantName; ?>!</h2>
                    <p>We have received your job application. Our hiring team will review it, and if your expertise matches
                        our requirement, we will contact you shortly.</p>
                    <button class="career-success-btn" type="button" data-career-success-close>Continue</button>
                </div>
            </div>
        <?php endif; ?>

        <section class="career-hero">
            <div class="career-container hero-grid">
                <div>
                    <span class="eyebrow"><i class="fa-solid fa-briefcase"></i> Careers at AITF</span>
                    <h1>Build a career that raises standards.</h1>
                    <p>Join a purpose-driven team helping organizations improve quality, compliance, safety and
                        performance through professional assessment and certification services.</p>
                    <div class="hero-actions">
                        <a class="career-btn primary" href="#apply-now"><i class="fa-solid fa-paper-plane"></i> Apply
                            Now</a>
                        <a class="career-btn secondary" href="#why-work"><i class="fa-solid fa-circle-info"></i> Why
                            Work With Us?</a>
                    </div>
                </div>
                <div class="hero-card">
                    <h3>Grow with meaningful work</h3>
                    <div class="hero-stat-grid">
                        <div class="hero-stat"><strong>Audit</strong><span>Professional assignments</span></div>
                        <div class="hero-stat"><strong>Learn</strong><span>Continuous improvement</span></div>
                        <div class="hero-stat"><strong>Impact</strong><span>Real client outcomes</span></div>
                        <div class="hero-stat"><strong>Lead</strong><span>Future-ready expertise</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section career-clean-section" id="why-work">
            <div class="career-container">
                <div class="career-section-header">
                    <span class="career-section-kicker"><i class="fa-solid fa-circle-check"></i> Why Work With
                        Us?</span>
                    <h2>A clear path to learn, grow and create meaningful impact.</h2>
                    <p>AITF gives professionals a practical platform to build technical expertise, work with diverse
                        industries and contribute to reliable certification outcomes.</p>
                </div>
                <div class="why-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <h3>Career Growth</h3>
                        <p>Develop assessment, auditing and client-handling skills through real assignments, senior
                            guidance and continuous learning.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-handshake"></i></div>
                        <h3>Supportive Team</h3>
                        <p>Work with a collaborative team that values fairness, knowledge sharing, disciplined execution
                            and professional integrity.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h3>Trusted Work</h3>
                        <p>Contribute to transparent, impartial and value-added assessments for organizations across
                            different sectors.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section impact-section">
            <div class="career-container impact-card">
                <div>
                    <span class="eyebrow"><i class="fa-solid fa-bolt"></i> Ready to make an impact?</span>
                    <h2>Your expertise can improve businesses.</h2>
                    <p>Every audit, review and client interaction is an opportunity to achieve organizations toward
                        stronger systems, safer workplaces and better outcomes.</p>
                </div>
                <div class="impact-list">
                    <div><i class="fa-solid fa-check"></i> Value added assessments to improve the management system
                        performance.</div>
                    <div><i class="fa-solid fa-check"></i> Build confidence through accredited assessment.</div>
                    <div><i class="fa-solid fa-check"></i> Be part of a team committed to continual improvement.</div>
                </div>
            </div>
        </section>

        <section class="section career-clean-section alt">
            <div class="career-container">
                <div class="career-section-header">
                    <span class="career-section-kicker"><i class="fa-solid fa-heart"></i> Why You'll Love Working
                        Here</span>
                    <h2>Professional environment, practical learning and meaningful responsibility.</h2>
                    <p>You will work in a focused environment where learning is practical, responsibilities are clear
                        and every contribution supports better client outcomes.</p>
                </div>
                <div class="love-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                        <h3>Learning Culture</h3>
                        <p>Stay updated with standards, processes and industry best practices while applying them in
                            day-to-day work.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-compass"></i></div>
                        <h3>Clear Purpose</h3>
                        <p>Work on assignments that support organizational excellence, stronger systems and continual
                            improvement.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fa-solid fa-people-group"></i></div>
                        <h3>Respect & Ownership</h3>
                        <p>Take responsibility with confidence in a respectful team culture that encourages disciplined
                            decision-making.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" style="background: #ffffff;">
            <div class="career-container director-wrap">
                <div class="director-card">
                    <div class="director-avatar"><i class="fa-solid fa-user-tie"></i></div>
                    <h3>Message from our Managing Director</h3>
                    <span>AITF Management Certification</span>
                </div>
                <div class="director-message">
                    <p style="color: #1e293b; font-size: 17px; line-height: 1.8; font-weight: 500;">At AITF, we believe
                        that great organizations are built by people who are curious, disciplined and committed to doing
                        the right thing. If you have the passion to learn and the courage to take responsibility, this
                        is a place where your work will matter.</p>
                    <p style="color: #1e293b; font-size: 17px; line-height: 1.8; font-weight: 500;">We welcome
                        professionals who want to grow with integrity, serve clients with excellence and contribute to a
                        stronger culture of quality and compliance. Your knowledge can create real impact, and we are
                        ready to support your journey.</p>
                    <p style="color: #0f172a; font-size: 17px; line-height: 1.8; font-weight: 500;"><strong
                            style="color: #0f172a;">Come prepared to learn, contribute and lead. Together, we can build
                            trust and raise standards.</strong></p>
                </div>
            </div>
        </section>

        <section class="section apply-section" id="apply-now">
            <div class="career-container apply-grid">
                <div class="apply-info">
                    <h2>Apply for open roles</h2>
                    <p>Share your details and our hiring team will review your application. If your expertise matches
                        our requirement, we will contact you shortly.</p>
                    <div>
                        <span class="role-pill"><i class="fa-solid fa-clipboard-check"></i> Auditor</span>
                        <span class="role-pill"><i class="fa-solid fa-person-chalkboard"></i> Facilitator</span>
                    </div>
                </div>
                <div class="career-form-card">
                    <div class="form-heading">
                        <span class="form-heading-icon"><i class="fa-solid fa-file-signature"></i></span>
                        <div>
                            <h3>Start your application</h3>
                            <p>Fill in your details below. We’ll review your profile and get back to you for suitable
                                roles.</p>
                        </div>
                    </div>
                    <?php if ($errorMessage !== ''): ?>
                        <div class="form-alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <form id="careerApplicationForm" action="submitJobApplication.php" method="post" autocomplete="on">
                        <div class="field">
                            <label for="applicant_name">Applicant Name</label>
                            <input type="text" id="applicant_name" name="applicant_name"
                                placeholder="Enter your full name" required>
                        </div>
                        <div class="form-row">
                            <div class="field">
                                <label for="email">Mail ID</label>
                                <input type="email" id="email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="field">
                                <label for="contact_no">Contact No.</label>
                                <input type="tel" id="contact_no" name="contact_no" placeholder="10 digit mobile number"
                                    required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="apply_for">Attach Your CV Here</label>
                            <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required
                                style="padding: 18px 15px;">
                        </div>
                        <div class="field">
                            <label for="message">Short Message <span
                                    style="color:#64748b; text-transform:none; font-weight:600; font-size: 12px;">(optional)</span></label>
                            <textarea id="message" name="message"
                                placeholder="Tell us briefly about your expertise or experience"
                                style="font-size: 15px;"></textarea>
                        </div>

                        <?php if ($recaptchaFrontendEnabled): ?>
                            <div class="captcha-box">
                                <?php if (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v3'): ?>
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                                <?php else: ?>
                                    <div class="g-recaptcha"
                                        data-sitekey="<?php echo htmlspecialchars($recaptchaConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-theme="<?php echo htmlspecialchars($recaptchaConfig['theme'] ?? 'light', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-callback="enableSubmitBtn"></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <button class="submit-btn" id="submitBtn" type="submit" <?php if ($recaptchaFrontendEnabled && ($recaptchaConfig['version'] ?? 'v2_checkbox') !== 'v3'): ?>disabled<?php endif; ?>
                            style="<?php if ($recaptchaFrontendEnabled && ($recaptchaConfig['version'] ?? 'v2_checkbox') !== 'v3'): ?>opacity: 0.5; cursor: not-allowed;<?php endif; ?>"><i
                                class="fa-solid fa-paper-plane"></i> Submit Application</button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <?php include './footerSource.php'; ?>

    <?php if ($successApplicant !== ''): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var successMessage = document.getElementById('careerSuccessMessage');
                var closeButtons = document.querySelectorAll('[data-career-success-close]');

                function closeSuccessMessage() {
                    if (!successMessage) {
                        return;
                    }
                    successMessage.classList.add('is-hiding');
                    window.setTimeout(function () {
                        if (successMessage && successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 200);
                }

                closeButtons.forEach(function (button) {
                    button.addEventListener('click', closeSuccessMessage);
                });

                if (successMessage) {
                    successMessage.addEventListener('click', function (event) {
                        if (event.target === successMessage) {
                            closeSuccessMessage();
                        }
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeSuccessMessage();
                    }
                });

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, 'career.php');
                }
            });
        </script>
    <?php endif; ?>

    <?php if ($recaptchaFrontendEnabled && (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v3')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.getElementById('careerApplicationForm');
                var tokenInput = document.getElementById('g-recaptcha-response');
                if (!form || !tokenInput || typeof grecaptcha === 'undefined') {
                    return;
                }
                form.addEventListener('submit', function (event) {
                    if (tokenInput.value) {
                        return;
                    }
                    event.preventDefault();
                    grecaptcha.ready(function () {
                        grecaptcha.execute('<?php echo addslashes($recaptchaConfig['site_key']); ?>', { action: '<?php echo addslashes($recaptchaConfig['action'] ?? 'submit'); ?>' }).then(function (token) {
                            tokenInput.value = token;
                            form.submit();
                        });
                    });
                });
            });
        </script>
    <?php endif; ?>

    <script>
        // Enable submit button after captcha verification
        function enableSubmitBtn() {
            var submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
        }
    </script>

</html>